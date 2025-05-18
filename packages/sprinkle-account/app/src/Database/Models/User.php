<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models;

use Carbon\Carbon;
use DateTime;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\HasherInterface;
use UserFrosting\Sprinkle\Account\Database\Factories\UserFactory;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PermissionInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PersistenceInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough;

/**
 * User Class.
 *
 * Represents a User object as stored in the database.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class User extends Model implements UserInterface
{
    use SoftDeletes {
        forceDelete as forceDeleteSoftModel;
    }
    use HasFactory;

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'users';

    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_name',
        'first_name',
        'last_name',
        'email',
        'locale',
        'group_id',
        'flag_verified',
        'flag_enabled',
        'password',
        'deleted_at',
    ];

    /**
     * @var string[] A list of attributes to hide by default when using toArray() and toJson().
     */
    protected $hidden = [
        'password',
        'password_last_set',
    ];

    /**
     * @var string[] The attributes that should be mutated to dates.
     */
    protected $dates = [
        'deleted_at',
        'password_last_set',
    ];

    /**
     * @var string[] The accessors to append to the model's array form.
     */
    protected $appends = [
        'full_name',
        'avatar',
    ];

    /**
     * @var array<string, string> The attributes that should be cast.
     */
    protected $casts = [
        'flag_verified' => 'boolean',
        'flag_enabled'  => 'boolean',
        'group_id'      => 'integer',
    ];

    /**
     * @var array<string, string> Events used to handle the user object cache on update and deletion.
     */
    protected $dispatchesEvents = [
        'saved'   => Events\DeleteUserCacheEvent::class,
        'deleted' => Events\DeleteUserCacheEvent::class,
    ];

    /**
     * Force delete this user from the database, along with any linked relations.
     *
     * @return bool|null
     */
    public function forceDelete()
    {
        // Remove all role associations
        $this->roles()->detach();

        // Remove all user info
        $this->activities()->delete(); // @phpstan-ignore-line Laravel magic method
        $this->persistences()->delete(); // @phpstan-ignore-line Laravel magic method

        // Delete the user
        return $this->forceDeleteSoftModel();
    }

    /**
     * Allows you to get the full name of the user using `$user->full_name`.
     *
     * @return string
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Allows you to get the user's avatar using `$user->avatar`.
     *
     * Use Gravatar as the user avatar provider.
     *
     * @return string
     */
    public function getAvatarAttribute(): string
    {
        $email = $this->email ?? '';
        $hash = md5(strtolower(trim($email)));

        return 'https://www.gravatar.com/avatar/' . $hash . '?d=mm';
    }

    /**
     * Attribute alias for lastActivity() method. Can be accessed using `$user->last_activity`.
     *
     * @return Activity|null
     */
    public function getLastActivityAttribute(): ?Activity
    {
        return $this->lastActivity();
    }

    /**
     * Attribute returning the user's data for frontend API responses.
     *
     * The frontend code will receive this data when the user is authenticated.
     * Add the user's permissions and master status to it's normal attributes.
     *
     * Use `$user->apiData()` to access this attribute.
     *
     * @return array<string, mixed>
     */
    public function getApiDataAttribute(): array
    {
        return array_merge(
            $this->attributesToArray(),
            ['permissions' => $this->getCachedPermissions()],
            ['is_master'   => $this->isMaster()]
        );
    }

    /**
     * Mutate password before saving into db. This is where password is hashed.
     *
     * @param string $value
     */
    public function setPasswordAttribute(string $value): void
    {
        /** @var HasherInterface */
        $hasher = static::$ci?->get(HasherInterface::class);

        // TODO : Might be worth using null instead.
        if ($value !== '') {
            $value = $hasher->hash($value);
        }

        $this->attributes['password'] = $value;
        $this->attributes['password_last_set'] = Carbon::now();
    }

    /**
     * Compare password to the user hashed password. Returns true if both evaluate to the same.
     *
     * @param string $password
     *
     * @return bool
     */
    public function comparePassword(string $password): bool
    {
        /** @var HasherInterface */
        $hasher = static::$ci?->get(HasherInterface::class);

        return $hasher->verify($password, $this->password);
    }

    /**
     * Return a cache instance specific to that user.
     *
     * @return Cache
     */
    public function getCache(): Cache
    {
        /** @var Cache */
        $cache = static::$ci?->get(Cache::class);

        return $cache->tags('_u' . $this->id);
    }

    /**
     * Return a cached version of the user. If not cached, fetch from db.
     *
     * @param int $id
     *
     * @return ?self
     */
    public static function findCached(int $id): ?self
    {
        /** @var Cache */
        $cache = static::$ci?->get(Cache::class);

        /** @var Config */
        $config = static::$ci?->get(Config::class);

        // Get config values
        $key = $config->get('cache.user.key') . $id;
        $delay = $config->get('cache.user.delay');

        /** @var self */
        return $cache->remember($key, $delay * 60, function () use ($id) {
            return self::find($id);
        });
    }

    /**
     * Forget cached version of this user.
     *
     * @return static
     */
    public function forgetCache(): static
    {
        /** @var Cache */
        $cache = static::$ci?->get(Cache::class);

        /** @var Config */
        $config = static::$ci?->get(Config::class);

        // Forget the cached version of the user
        $key = $config->get('cache.user.key') . $this->id;
        $cache->forget($key);

        // Forget all user cache
        // @phpstan-ignore-next-line
        $this->getCache()->flush();

        return $this;
    }

    /**
     * Retrieve the cached permissions dictionary for this user.
     *
     * @return array<string, string[]>
     */
    public function getCachedPermissions(): array
    {
        return $this->getCache()->rememberForever('permissions', function () {
            return $this->buildPermissionsDictionary();
        });
    }

    /**
     * Retrieve the cached permissions dictionary for this user.
     *
     * @return static
     */
    public function reloadCachedPermissions(): static
    {
        $this->getCache()->forget('permissions');
        $this->getCachedPermissions();

        return $this;
    }

    /**
     * Returns whether or not this user is the master user.
     *
     * @return bool
     */
    public function isMaster(): bool
    {
        /** @var Config */
        $config = static::$ci?->get(Config::class);
        $masterId = intval($config->get('reserved_user_ids.master'));

        // Need to use loose comparison for now, because some DBs return `id` as a string
        return $this->id == $masterId;
    }

    /**
     * Returns whether or not this user's password is expired.
     *
     * @return bool
     */
    public function isPasswordExpired(): bool
    {
        // If the password is empty, it is expired
        if ($this->password === '') {
            return true;
        }

        // If the password was never set, it is expired
        if ($this->password_last_set === null) {
            return true;
        }

        // Determine if the user's password has expired based on the configured
        // expiration timeout and the date the password was last set.
        /** @var Config */
        $config = static::$ci?->get(Config::class);
        $expirationTimeout = $config->getInt('user.password.expiration.timeout', 0);

        if ($expirationTimeout <= 0) {
            return false;
        }

        return $this->password_last_set->addDays($expirationTimeout) < Carbon::now();
    }

    /**
     * Get all activities for this user.
     *
     * @return HasMany
     */
    public function activities(): HasMany
    {
        /** @var string */
        $relation = static::$ci?->get(ActivityInterface::class);

        // Define foreign key in case User is extended
        return $this->hasMany($relation, 'user_id');
    }

    /**
     * Get the most recent activity for this user.
     *
     * @param string|null $type The type of activity to search for.
     *
     * @return Activity|null
     */
    public function lastActivity(?string $type = null): ?Activity
    {
        $query = $this->activities();
        if (!is_null($type)) {
            // @phpstan-ignore-next-line Laravel is bad at type hinting
            $query = $query->forType($type);
        }

        // @phpstan-ignore-next-line Laravel is bad at type hinting
        return $query->orderBy('occurred_at', 'desc')->first();
    }

    /**
     * Get the most recent time for a specified activity type for this user.
     *
     * @param string|null $type The type of activity to search for.
     *
     * @return DateTime|null The last activity time, as a DateTime, or null if an activity of this type doesn't exist.
     */
    public function lastActivityTime(?string $type = null): ?DateTime
    {
        return $this->lastActivity($type)?->occurred_at;
    }

    /**
     * Get the amount of time, in seconds, that has elapsed since the last activity of a certain time for this user.
     *
     * @param string|null $type The type of activity to search for.
     *
     * @return int
     */
    public function getSecondsSinceLastActivity(?string $type = null): int
    {
        $time = $this->lastActivityTime($type) ?? '0000-00-00 00:00:00';
        $time = new Carbon($time);

        return $time->diffInSeconds();
    }

    /**
     * Joins the user's most recent activity directly, so we can do things like
     * sort, search, paginate, etc. in Sprunje. Also add `last_activity` columns
     * for sorting users by last activity.
     *
     * @param Builder $query
     *
     * @return Builder|QueryBuilder
     */
    public function scopeJoinLastActivity(Builder $query): Builder|QueryBuilder
    {
        return $query->select('users.*', new Expression('MAX(activities.occurred_at) as last_activity'))
                     ->join('activities', 'activities.user_id', '=', 'users.id')
                     ->groupBy('users.id');
    }

    /**
     * Return this user's group.
     *
     * @return BelongsTo
     */
    public function group(): BelongsTo
    {
        /** @var string */
        $relation = static::$ci?->get(GroupInterface::class);

        // Define foreign key in case User is extended
        return $this->belongsTo($relation);
    }

    /**
     * Get all persistence items for this user.
     *
     * @return HasMany
     */
    public function persistences(): HasMany
    {
        /** @var string */
        $relation = static::$ci?->get(PersistenceInterface::class);

        // Define foreign key in case User is extended
        return $this->hasMany($relation, 'user_id');
    }

    /**
     * Get all of the permissions this user has, through its roles.
     *
     * @return BelongsToManyThrough
     */
    public function permissions(): BelongsToManyThrough
    {
        /** @var class-string */
        $permissionRelation = static::$ci?->get(PermissionInterface::class);

        /** @var class-string */
        $roleRelation = static::$ci?->get(RoleInterface::class);

        // Define foreign keys in case User is extended
        return $this->belongsToManyThrough(
            $permissionRelation,
            $roleRelation,
            firstJoiningTable: 'role_users',
            firstForeignPivotKey: 'user_id',
            secondJoiningTable: 'permission_roles',
        );
    }

    /**
     * Get all roles to which this user belongs.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        /** @var string */
        $relation = static::$ci?->get(RoleInterface::class);

        // Define foreign keys in case User is extended
        return $this->belongsToMany($relation, 'role_users', 'user_id')
                    ->using(RoleUsers::class)
                    ->withTimestamps();
    }

    /**
     * Query scope to get all users who have a specific role.
     *
     * @param Builder           $query
     * @param int|RoleInterface $role
     *
     * @return Builder|QueryBuilder
     */
    public function scopeForRole(Builder $query, int|RoleInterface $role): Builder|QueryBuilder
    {
        if ($role instanceof RoleInterface) {
            $roleId = $role->id;
        } else {
            $roleId = $role;
        }

        return $query->whereHas('roles', function ($q) use ($roleId) {
            $q->where('roles.id', $roleId);
        });
    }

    /**
     * Loads permissions for this user into a cached dictionary of slugs -> arrays of permissions,
     * so we don't need to keep requerying the DB for every call of checkAccess.
     *
     * @return array<string, string[]>
     */
    protected function buildPermissionsDictionary(): array
    {
        $permissions = [];

        /** @var PermissionInterface $permission */
        foreach ($this->permissions()->get() as $permission) {
            $permissions[$permission->slug][] = $permission->conditions;
        }

        return $permissions;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<User>
     */
    protected static function newFactory(): Factory
    {
        return UserFactory::new();
    }
}
