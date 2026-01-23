<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @method static create(array $array)
 *
 * @property int $id
 * @property int|null $club_id
 * @property int|null $player_id
 * @property int|null $matchday_id
 * @property int|null $fee_entry_id
 * @property TransactionType $type
 * @property int $amount
 * @property string $date
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Club|null $club
 * @property-read \App\Models\FeeEntry|null $feeEntry
 * @property-read \App\Models\Matchday|null $matchday
 * @property-read \App\Models\Player|null $player
 *
 * @method static \Database\Factories\TransactionFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereClubId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereFeeEntryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereMatchdayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction wherePlayerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transaction whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'club_id',
        'player_id',
        'fee_type_id',
        'matchday_id',
        'fee_entry_id',
        'type',
        'date',
        'amount',
        'notes',
    ];

    protected $casts = [
        'type' => TransactionType::class,
    ];

    public function club(): BelongsTo
    {
        return $this->BelongsTo(Club::class);
    }

    public function player(): BelongsTo
    {
        return $this->BelongsTo(Player::class);
    }

    public function feeEntry(): BelongsTo
    {
        return $this->BelongsTo(FeeEntry::class);
    }

    public function matchday(): BelongsTo
    {
        return $this->BelongsTo(Matchday::class);
    }

    public function setAmountAttribute($value)
    {
        $this->attributes['amount'] = (int) round($value * 100);
    }

    public function getAmountAttribute($value)
    {
        return $value / 100;
    }
}
