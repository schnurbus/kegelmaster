<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * App\Models\Player
 *
 * @property int $id
 * @property string $name
 * @property int $club_id
 * @property int|null $user_id
 * @property int $role_id
 * @property int $sex
 * @property float $balance
 * @property float $initial_balance
 * @property bool $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Club $club
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeeEntry> $feeEntries
 * @property-read int|null $fee_entries_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Matchday> $matchdays
 * @property-read int|null $matchdays_count
 * @property-read \App\Models\Role $role
 * @property-read \App\Models\User|null $user
 *
 * @method static \Database\Factories\PlayerFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereClubId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereInitialBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereSex($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereUserId($value)
 *
 * @property int $bouncer_role_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Player whereBouncerRoleId($value)
 *
 * @mixin \Eloquent
 */
class Player extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\PlayerFactory> */
    use Authorizable, HasFactory, HasRoles;

    protected $guard_name = 'player';

    protected $fillable = [
        'name',
        'club_id',
        'user_id',
        'sex',
        'active',
        'role_id',
        'initial_balance',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($player) {
            $player->balance = $player->initial_balance;
        });
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function matchdays(): BelongsToMany
    {
        return $this->belongsToMany(Matchday::class, 'matchday_player');
    }

    public function feeEntries(): HasMany
    {
        return $this->hasMany(FeeEntry::class);
    }

    protected function initialBalance(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => (float) ($value / 100),
            set: fn (float $value) => (int) round($value * 100),
        );
    }

    protected function balance(): Attribute
    {
        return Attribute::make(
            get: fn (int $value) => (float) ($value / 100),
            set: fn (float $value) => (int) round($value * 100),
        );
    }
}
