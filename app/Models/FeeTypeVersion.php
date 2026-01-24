<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\FeeType
 *
 * @property int $id
 * @property string $name
 * @property float $amount
 * @property string $description
 * @property int $fee_type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeeEntry> $feeEntries
 * @property-read int|null $fee_entries_count
 * @property-read \App\Models\FeeType $feeType
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Matchday> $matchdays
 * @property-read int|null $matchdays_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion whereFeeTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeTypeVersion whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FeeTypeVersion extends Model
{
    protected $fillable = ['fee_type_id', 'name', 'description', 'amount'];

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function matchdays(): BelongsToMany
    {
        return $this->belongsToMany(Matchday::class, 'fee_type_version_matchday');
    }

    public function feeEntries(): HasMany
    {
        return $this->hasMany(FeeEntry::class);
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => (float) ($value / 100),
            set: fn (float $value) => (int) round($value * 100),
        );
    }
}
