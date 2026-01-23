<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Club
 *
 * @property int $id
 * @property string $name
 * @property float $base_fee
 * @property float $initial_balance
 * @property float $balance
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CompetitionType> $competitionTypes
 * @property-read int|null $competition_types_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Matchday> $matchdays
 * @property-read int|null $matchdays_count
 * @property-read \App\Models\User $owner
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Player> $players
 * @property-read int|null $players_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Transaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read \App\Models\User $user
 *
 * @method static \Database\Factories\ClubFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereBaseFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereInitialBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Club whereUserId($value)
 *
 * @mixin \Eloquent
 */
class Club extends Model
{
    /** @use HasFactory<\Database\Factories\ClubFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'base_fee',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function competitionTypes(): HasMany
    {
        return $this->hasMany(CompetitionType::class);
    }

    public function matchdays(): HasMany
    {
        return $this->hasMany(Matchday::class);
    }

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function setInitialBalanceAttribute($value)
    {
        $this->attributes['initial_balance'] = (int) round($value * 100);
    }

    public function getInitialBalanceAttribute($value)
    {
        return (float) ($value / 100);
    }

    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn (int $value): float => (float) ($value / 100),
            set: fn (float $value) => (int) round($value * 100),
        );
    }

    // public function setBalanceAttribute($value)
    // {
    //     $this->attributes['balance'] = $value * 100;
    // }

    // public function getBalanceAttribute($value)
    // {
    //     return $value / 100;
    // }

    public function setBaseFeeAttribute($value)
    {
        $this->attributes['base_fee'] = (int) round($value * 100);
    }

    public function getBaseFeeAttribute($value)
    {
        return (float) ($value / 100);
    }
}
