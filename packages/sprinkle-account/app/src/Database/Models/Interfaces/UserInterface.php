<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models\Interfaces;

use DateTime;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough;

/**
 * User Model Interface.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \UserFrosting\Sprinkle\Core\Database\Models\Model
 *
 * @property int                              $id
 * @property string                           $user_name
 * @property string                           $first_name
 * @property string                           $last_name
 * @property string                           $full_name
 * @property string                           $email
 * @property string                           $locale
 * @property int|null                         $group_id
 * @property bool                             $flag_verified
 * @property bool                             $flag_enabled
 * @property string                           $password
 * @property \Carbon\Carbon|null              $password_last_set
 * @property string                           $avatar
 * @property timestamp                        $created_at
 * @property timestamp                        $updated_at
 * @property timestamp|null                   $deleted_at
 * @property GroupInterface|null              $group
 * @property Collection<ActivityInterface>    $activities
 * @property Collection<PersistenceInterface> $persistences
 * @property Collection<PermissionInterface>  $permissions
 * @property Collection<RoleInterface>        $roles
 * @property ActivityInterface|null           $last_activity
 * @property ActivityInterface|null           $lastActivity
 * @property array<string, mixed>             $apiData
 *
 * @method        $this joinLastActivity()
 * @method static $this joinLastActivity()
 * @method        $this forRole(int|RoleInterface $role)
 * @method static $this forRole(int|RoleInterface $role)
 */
interface UserInterface
{
    /**
     * Allows you to get the full name of the user using `$user->full_name`.
     *
     * @return string
     */
    public function getFullNameAttribute();

    /**
     * Allows you to get the user's avatar using `$user->avatar`.
     *
     * Use Gravatar as the user avatar provider.
     *
     * @return string
     */
    public function getAvatarAttribute(): string;

    /**
     * Attribute alias for lastActivity() method. Can be accessed using `$user->last_activity`.
     *
     * @return Activity|null
     */
    public function getLastActivityAttribute(): ?Activity;

    /**
     * Mutate password before saving into db. This is where password is hashed.
     *
     * @param string $value
     */
    public function setPasswordAttribute(string $value): void;

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
    public function getApiDataAttribute(): array;

    /**
     * Compare password to the user hashed password. Returns true if both evaluate to the same.
     *
     * @param string $password
     *
     * @return bool
     */
    public function comparePassword(string $password): bool;

    /**
     * Return a cache instance specific to that user.
     *
     * @return Cache
     */
    public function getCache(): Cache;

    /**
     * Return a cached version of the user. If not cached, fetch from db.
     *
     * @param int $id
     *
     * @return ?self
     */
    public static function findCached(int $id): ?self;

    /**
     * Forget cached version of this user.
     *
     * @return static
     */
    public function forgetCache(): static;

    /**
     * Retrieve the cached permissions dictionary for this user.
     *
     * @return array<string, string[]>
     */
    public function getCachedPermissions(): array;

    /**
     * Retrieve the cached permissions dictionary for this user.
     *
     * @return static
     */
    public function reloadCachedPermissions(): static;

    /**
     * Returns whether or not this user is the master user.
     *
     * @return bool
     */
    public function isMaster(): bool;

    /**
     * Returns whether or not this user's password is expired.
     *
     * @return bool
     */
    public function isPasswordExpired(): bool;

    /**
     * Get all activities for this user.
     *
     * @return HasMany
     */
    public function activities(): HasMany;

    /**
     * Get the most recent activity for this user.
     *
     * @param string|null $type The type of activity to search for.
     *
     * @return Activity|null
     */
    public function lastActivity(?string $type = null): ?Activity;

    /**
     * Get the most recent time for a specified activity type for this user.
     *
     * @param string|null $type The type of activity to search for.
     *
     * @return DateTime|null The last activity time, as a DateTime, or null if an activity of this type doesn't exist.
     */
    public function lastActivityTime(?string $type = null): ?DateTime;

    /**
     * Get the amount of time, in seconds, that has elapsed since the last activity of a certain time for this user.
     *
     * @param string|null $type The type of activity to search for.
     *
     * @return int
     */
    public function getSecondsSinceLastActivity(?string $type = null): int;

    /**
     * Joins the user's most recent activity directly, so we can do things like
     * sort, search, paginate, etc. in Sprunje.
     *
     * @param Builder $query
     *
     * @return Builder|QueryBuilder
     */
    public function scopeJoinLastActivity(Builder $query): Builder|QueryBuilder;

    /**
     * Return this user's group.
     *
     * @return BelongsTo
     */
    public function group(): BelongsTo;

    /**
     * Performs tasks to be done after this user has been successfully authenticated.
     *
     * By default, adds a new sign-in activity and updates any legacy hash.
     *
     * @param mixed[] $params Optional array of parameters used for this event handler.
     */
    // public function onLogin($params = []);

    /**
     * Performs tasks to be done after this user has been logged out.
     *
     * By default, adds a new sign-out activity.
     *
     * @param mixed[] $params Optional array of parameters used for this event handler.
     */
    // public function onLogout($params = []);

    /**
     * Get all of the permissions this user has, through its roles.
     *
     * @return BelongsToManyThrough
     */
    public function permissions(): BelongsToManyThrough;

    /**
     * Get all persistence items for this user.
     *
     * @return HasMany
     */
    public function persistences(): HasMany;

    /**
     * Get all roles to which this user belongs.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany;

    /**
     * Query scope to get all users who have a specific role.
     *
     * @param Builder           $query
     * @param int|RoleInterface $role
     *
     * @return Builder|QueryBuilder
     */
    public function scopeForRole(Builder $query, int|RoleInterface $role): Builder|QueryBuilder;
}
