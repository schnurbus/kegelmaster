<?php

use App\Enums\TransactionType;
use App\Models\Club;
use App\Models\Player;
use App\Models\Role;
use App\Models\Transaction;
use App\Models\User;
use App\Services\ClubService;
use App\Services\PlayerService;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->clubService = Mockery::mock(ClubService::class);
    $this->playerService = Mockery::mock(PlayerService::class);

    $this->transactionService = new TransactionService(
        $this->clubService,
        $this->playerService
    );
});

afterEach(function () {
    Mockery::close();
});

it('creates a expense transaction without player_id', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create(['user_id' => $user->id]);

    $transactionData = [
        'club_id' => $club->id,
        'amount' => 100,
        'type' => TransactionType::EXPENSE,
        'date' => now()->format('Y-m-d'),
        'notes' => 'Test expense transaction',
    ];

    $this->clubService->shouldReceive('recalculateBalance')
        ->once()
        ->with(Mockery::type(Club::class));

    $transaction = $this->transactionService->createTransaction($transactionData);

    expect($transaction)->toBeInstanceOf(Transaction::class)
        ->and($transaction->type)->toBe(TransactionType::EXPENSE)
        ->and($transaction->amount)->toBe(100.0)
        ->and($transaction->player_id)->toBeNull()
        ->and($transaction->club_id)->toBe($club->id);
});

it('throws exception when player_id is missing for non-expense transaction', function () {
    $user = User::factory()->create();
    $club = Club::factory()->create(['user_id' => $user->id]);

    $transactionData = [
        'club_id' => $club->id,
        'amount' => 100,
        'type' => TransactionType::FEE,
        'date' => now()->format('Y-m-d'),
    ];

    expect(fn () => $this->transactionService->createTransaction($transactionData))
        ->toThrow(
            \InvalidArgumentException::class,
            'player_id is required for transaction type '.TransactionType::FEE->label()
        );
});

it('creates a simple payment transaction for single player', function () {
    $club = Club::factory()->create();
    $role = Role::factory()->create(['club_id' => $club->id]);
    $player = Player::factory()->create([
        'role_id' => $role->id,
        'initial_balance' => -50,
    ]);

    $this->playerService->shouldReceive('recalculateBalance')
        ->once()
        ->with(Mockery::type(Player::class));

    $this->clubService->shouldReceive('recalculateBalance')
        ->once()
        ->with(Mockery::type(Club::class));

    $transactionData = [
        'club_id' => $player->club->id,
        'player_id' => $player->id,
        'amount' => 30,
        'type' => TransactionType::PAYMENT,
        'date' => now()->format('Y-m-d'),
    ];

    $transaction = $this->transactionService->createTransaction($transactionData);

    expect($transaction)->toBeInstanceOf(Transaction::class)
        ->and($transaction->type)->toBe(TransactionType::PAYMENT)
        ->and($transaction->amount)->toBe(30.0)
        ->and($transaction->player_id)->toBe($player->id);
});

it('creates payment with tip when amount exceeds balance and auto_tip is true', function () {
    $player = Player::factory()->create([
        'initial_balance' => -50,
    ]);

    $this->playerService->shouldReceive('recalculateBalance')
        ->once()
        ->with(Mockery::type(Player::class));

    $this->clubService->shouldReceive('recalculateBalance')
        ->once()
        ->with(Mockery::type(Club::class));

    $transactionData = [
        'club_id' => $player->club->id,
        'player_id' => $player->id,
        'amount' => 70,
        'type' => TransactionType::PAYMENT,
        'date' => now()->format('Y-m-d'),
        'auto_tip' => true,
    ];

    $transaction = $this->transactionService->createTransaction($transactionData);

    expect($transaction)->toBeInstanceOf(Transaction::class)
        ->and($transaction->type)->toBe(TransactionType::PAYMENT)
        ->and($transaction->amount)->toBe(50.0);

    $tipTransaction = Transaction::where('player_id', $player->id)
        ->where('type', TransactionType::TIP)
        ->first();

    expect($tipTransaction)->not->toBeNull()
        ->and($tipTransaction->amount)->toBe(20.0);
});

it('distributes payment evenly among multiple players', function () {
    $club = Club::factory()->create();

    $player1 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -30,
    ]);

    $player2 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -40,
    ]);

    $player3 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -50,
    ]);

    $this->playerService->shouldReceive('recalculateBalance')
        ->times(3)
        ->with(Mockery::type(Player::class));

    $this->clubService->shouldReceive('recalculateBalance')
        ->times(3)
        ->with(Mockery::type(Club::class));

    $transactionData = [
        'club_id' => $club->id,
        'player_id' => [$player1->id, $player2->id, $player3->id],
        'amount' => 60,
        'type' => TransactionType::PAYMENT,
        'date' => now()->format('Y-m-d'),
    ];

    $transactions = $this->transactionService->createTransaction($transactionData);

    expect($transactions)->toBeArray()
        ->toHaveCount(3);

    $totalDistributed = 0;
    foreach ($transactions as $transaction) {
        expect($transaction->type)->toBe(TransactionType::PAYMENT);
        expect([$player1->id, $player2->id, $player3->id])->toContain($transaction->player_id);
        $totalDistributed += (float) $transaction->amount;
    }

    expect($totalDistributed)->toBe(60.0);
});

it('handles uneven distribution correctly', function () {
    $club = Club::factory()->create();

    $player1 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -30,
    ]);

    $player2 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -40,
    ]);

    $player3 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -50,
    ]);

    $this->playerService->shouldReceive('recalculateBalance')
        ->times(3)
        ->with(Mockery::type(Player::class));

    $this->clubService->shouldReceive('recalculateBalance')
        ->times(3)
        ->with(Mockery::type(Club::class));

    $transactionData = [
        'club_id' => $club->id,
        'player_id' => [$player1->id, $player2->id, $player3->id],
        'amount' => 50.01,
        'type' => TransactionType::PAYMENT,
        'date' => now()->format('Y-m-d'),
    ];

    $transactions = $this->transactionService->createTransaction($transactionData);

    expect($transactions)->toBeArray()
        ->toHaveCount(3);

    $totalDistributed = round(array_sum(array_map(function ($t) {
        return $t->amount;
    }, $transactions)), 2);

    expect($totalDistributed)->toBe(50.01);
});

it('creates transactions to zero balances and tip when amount exceeds total balance with auto_tip', function () {
    $club = Club::factory()->create();

    $player1 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -30,
    ]);

    $player2 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -40,
    ]);

    $this->playerService->shouldReceive('recalculateBalance')
        ->twice()
        ->with(Mockery::type(Player::class));

    $this->clubService->shouldReceive('recalculateBalance')
        ->times(3)
        ->with(Mockery::type(Club::class));

    $transactionData = [
        'club_id' => $club->id,
        'player_id' => [$player1->id, $player2->id],
        'amount' => 100,
        'type' => TransactionType::PAYMENT,
        'date' => now()->format('Y-m-d'),
        'auto_tip' => true,
    ];

    $transactions = $this->transactionService->createTransaction($transactionData);

    expect($transactions)->toBeArray()
        ->toHaveCount(3);

    $paymentTotal = 0;
    $tipFound = false;
    $tipAmount = 0;

    foreach ($transactions as $transaction) {
        if ($transaction->type === TransactionType::PAYMENT) {
            if ($transaction->player_id === $player1->id) {
                expect($transaction->amount)->toBe(30.0);
            } elseif ($transaction->player_id === $player2->id) {
                expect($transaction->amount)->toBe(40.0);
            }
            $paymentTotal += $transaction->amount;
        } elseif ($transaction->type === TransactionType::TIP) {
            $tipFound = true;
            $tipAmount = (float) $transaction->amount;
            expect($transaction->player_id)->toBe($player1->id);
        }
    }

    expect($paymentTotal)->toBe(70.0);
    expect($tipFound)->toBeTrue();
    expect($tipAmount)->toBe(30.0);
});

it('creates transactions to zero balances and distributes remainder when auto_tip is false', function () {
    $club = Club::factory()->create();
    $role = Role::factory()->create(['club_id' => $club->id]);

    $player1 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -30,
        'role_id' => $role->id,
    ]);

    $player2 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => -40,
        'role_id' => $role->id,
    ]);

    $this->playerService->shouldReceive('recalculateBalance')
        ->times(4)
        ->with(Mockery::type(Player::class));

    $this->clubService->shouldReceive('recalculateBalance')
        ->times(4)
        ->with(Mockery::type(Club::class));

    $transactionData = [
        'club_id' => $club->id,
        'player_id' => [$player1->id, $player2->id],
        'amount' => 100,
        'type' => TransactionType::PAYMENT,
        'date' => now()->format('Y-m-d'),
        'auto_tip' => false,
    ];

    $transactions = $this->transactionService->createTransaction($transactionData);

    expect($transactions)->toBeArray()
        ->toHaveCount(4);

    $paymentTotal = 0;
    $remainderTotal = 0;

    foreach ($transactions as $transaction) {
        if ($transaction->amount == 30 || $transaction->amount == 40) {
            $paymentTotal += $transaction->amount;
        } else {
            $remainderTotal += $transaction->amount;
        }
    }

    expect($paymentTotal)->toBe(70.0);
    expect($remainderTotal)->toBe(30.0);
});

it('throws exception for array of player_ids in non-payment transaction', function () {
    $club = Club::factory()->create();
    $role = Role::factory()->create(['club_id' => $club->id]);
    $players = Player::factory()->count(2)->create(['club_id' => $club->id, 'role_id' => $role->id]);

    $transactionData = [
        'club_id' => $club->id,
        'player_id' => $players->pluck('id')->toArray(),
        'amount' => 100,
        'type' => TransactionType::FEE,
        'date' => now()->format('Y-m-d'),
    ];

    expect(fn () => $this->transactionService->createTransaction($transactionData))
        ->toThrow(\InvalidArgumentException::class, 'Multiple player_ids are only supported for payment transactions');
});

it('throws exception when no valid players found', function () {
    $club = Club::factory()->create();

    $transactionData = [
        'club_id' => $club->id,
        'player_id' => [999, 1000],
        'amount' => 100,
        'type' => TransactionType::PAYMENT,
        'date' => now()->format('Y-m-d'),
    ];

    expect(fn () => $this->transactionService->createTransaction($transactionData))
        ->toThrow(\InvalidArgumentException::class, 'No valid players found');
});

it('throws exception when no players with debt found', function () {
    $club = Club::factory()->create();

    $player1 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => 10,
    ]);

    $player2 = Player::factory()->create([
        'club_id' => $club->id,
        'initial_balance' => 20,
    ]);

    $transactionData = [
        'club_id' => $club->id,
        'player_id' => [$player1->id, $player2->id],
        'amount' => 50,
        'type' => TransactionType::PAYMENT,
        'date' => now()->format('Y-m-d'),
    ];

    expect(fn () => $this->transactionService->createTransaction($transactionData))
        ->toThrow(\InvalidArgumentException::class, 'No players with debt found to distribute payment');
});

it('handles database transaction rollback on error', function () {
    $player = Player::factory()->create([
        'initial_balance' => -50,
    ]);

    $this->playerService->shouldReceive('recalculateBalance')
        ->andThrow(new Exception('Simulated error'));

    $transactionData = [
        'club_id' => $player->club->id,
        'player_id' => $player->id,
        'amount' => 30,
        'type' => TransactionType::PAYMENT,
        'auto_tip' => false,
        'date' => now()->format('Y-m-d'),
    ];

    $initialCount = Transaction::count();

    expect(fn () => $this->transactionService->createTransaction($transactionData))
        ->toThrow(Exception::class, 'Simulated error');

    expect(Transaction::count())->toBe($initialCount);
});
