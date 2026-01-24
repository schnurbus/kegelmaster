<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * App\Models\FeeType
 *
 * @property int $id
 * @property string $name
 * @property float $amount
 * @property int $position
 * @property string $description
 * @property int $club_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Club $club
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeeTypeVersion> $feeTypeVersions
 * @property-read int|null $fee_type_versions_count
 * @property-read \App\Models\FeeTypeVersion|null $latestVersion
 *
 * @method static \Database\Factories\FeeTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType whereClubId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|FeeType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class FeeType extends Model
{
    /** @use HasFactory<\Database\Factories\FeeTypeFactory> */
    use HasFactory;

    protected $fillable = ['club_id', 'name', 'description', 'amount', 'position'];

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function feeTypeVersions(): HasMany
    {
        return $this->hasMany(FeeTypeVersion::class);
    }

    public function latestVersion(): HasOne
    {
        return $this->hasOne(FeeTypeVersion::class)->latestOfMany();
    }

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => (float) ($value / 100),
            set: fn (float $value) => (int) round($value * 100),
        );
    }
}
