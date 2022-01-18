<?php

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2022 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use UserFrosting\Sprinkle\Account\Database\Factories\UserFactory;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PasswordResetInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PermissionInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
// use UserFrosting\Sprinkle\Account\Facades\Password;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * User Class.
 *
 * Represents a User object as stored in the database.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @property int       $id
 * @property string    $user_name
 * @property string    $first_name
 * @property string    $last_name
 * @property string    $full_name
 * @property string    $email
 * @property string    $locale
 * @property int       $group_id
 * @property bool      $flag_verified
 * @property bool      $flag_enabled
 * @property int       $last_activity_id
 * @property timestamp $created_at
 * @property timestamp $updated_at
 * @property string    $password
 * @property timestamp $deleted_at
 */
class User extends Model implements UserInterface
{
    use SoftDeletes;
    use HasFactory;

    /**
     * The name of the table for the current model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Fields that should be mass-assignable when creating a new User.
     *
     * @var string[]
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
        'last_activity_id',
        'password',
        'deleted_at',
    ];

    /**
     * A list of attributes to hide by default when using toArray() and toJson().
     *
     * @link https://laravel.com/docs/5.8/eloquent-serialization#hiding-attributes-from-json
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var string[]
     */
    protected $dates = [
        'deleted_at',
    ];

    protected $appends = [
        'full_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'flag_verified' => 'boolean',
        'flag_enabled'  => 'boolean',
    ];

    /**
     * Events used to handle the user object cache on update and deletion.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        // 'saved'   => Events\DeleteUserCacheEvent::class, // TODO
        // 'deleted' => Events\DeleteUserCacheEvent::class, // TODO
    ];

    /**
     * Cached dictionary of permissions for the user.
     *
     * @var array
     */
    protected $cachedPermissions;

    /**
     * Enable timestamps for Users.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * Determine if the property for this object exists.
     *
     * We add relations here so that Twig will be able to find them.
     * See http://stackoverflow.com/questions/29514081/cannot-access-eloquent-attributes-on-twig/35908957#35908957
     * Every property in __get must also be implemented here for Twig to recognize it.
     *
     * @param string $name the name of the property to check.
     *
     * @return bool true if the property is defined, false otherwise.
     */
    // TODO : Change to attribute
    // public function __isset($name)
    // {
    //     if (in_array($name, [
    //         'group',
    //         'last_sign_in_time',
    //         'avatar',
    //     ])) {
    //         return true;
    //     } else {
    //         return parent::__isset($name);
    //     }
    // }

    /**
     * Get a property for this object.
     *
     * @param string $name the name of the property to retrieve.
     *
     * @throws \Exception the property does not exist for this object.
     *
     * @return string the associated property.
     */
    // TODO : Change to attribute
    // public function __get($name)
    // {
    //     if ($name == 'last_sign_in_time') {
    //         return $this->lastActivityTime('sign_in');
    //     } elseif ($name == 'avatar') {
    //         // Use Gravatar as the user avatar
    //         $hash = md5(strtolower(trim($this->email)));

    //         return 'https://www.gravatar.com/avatar/' . $hash . '?d=mm';
    //     } else {
    //         return parent::__get($name);
    //     }
    // }

    /**
     * Get all activities for this user.
     *
     * @return ActivityInterface|HasMany
     */
    public function activities()
    {
        /** @var string */
        $relation = static::$ci->get(ActivityInterface::class);

        return $this->hasMany($relation, 'user_id');
    }

    /**
     * Delete this user from the database, along with any linked roles and activities.
     *
     * @param bool $hardDelete Set to true to completely remove the user and all associated objects.
     *
     * @return bool true if the deletion was successful, false otherwise.
     */
    public function delete($hardDelete = false)
    {
        if ($hardDelete) {

            /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
            $classMapper = static::$ci->classMapper;

            // Remove all role associations
            $this->roles()->detach();

            // Remove last activity association
            $this->lastActivity()->dissociate();
            $this->save();

            // Remove all user tokens
            $this->activities()->delete();
            $this->passwordResets()->delete();
            $classMapper->getClassMapping('verification')::where('user_id', $this->id)->delete();
            $classMapper->getClassMapping('persistence')::where('user_id', $this->id)->delete();

            // Delete the user
            return $this->forceDelete();
        }

        // Soft delete the user, leaving all associated records alone
        return parent::delete();
    }

    /**
     * Return a cache instance specific to that user.
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    public function getCache()
    {
        return static::$ci->cache->tags('_u' . $this->id);
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
     * Retrieve the cached permissions dictionary for this user.
     *
     * @return array
     */
    public function getCachedPermissions()
    {
        if (!isset($this->cachedPermissions)) {
            $this->reloadCachedPermissions();
        }

        return $this->cachedPermissions;
    }

    /**
     * Retrieve the cached permissions dictionary for this user.
     *
     * @return User
     */
    public function reloadCachedPermissions()
    {
        $this->cachedPermissions = $this->buildPermissionsDictionary();

        return $this;
    }

    /**
     * Get the amount of time, in seconds, that has elapsed since the last activity of a certain time for this user.
     *
     * @param string $type The type of activity to search for.
     *
     * @return int
     */
    public function getSecondsSinceLastActivity($type)
    {
        $time = $this->lastActivityTime($type);
        $time = $time ? $time : '0000-00-00 00:00:00';
        $time = new Carbon($time);

        return $time->diffInSeconds();
    }

    /**
     * Return this user's group.
     *
     * @return GroupInterface|BelongsTo
     */
    public function group()
    {
        /** @var string */
        $relation = static::$ci->get(GroupInterface::class);

        return $this->belongsTo($relation, 'group_id');
    }

    /**
     * Returns whether or not this user is the master user.
     *
     * @return bool
     */
    public function isMaster(): bool
    {
        /** @var Config */
        $config = static::$ci->get(Config::class);
        $masterId = intval($config->get('reserved_user_ids.master'));

        // Need to use loose comparison for now, because some DBs return `id` as a string
        return $this->id == $masterId;
    }

    /**
     * Get the most recent activity for this user, based on the user's last_activity_id.
     *
     * @return ActivityInterface|BelongsTo
     */
    public function lastActivity()
    {
        /** @var string */
        $relation = static::$ci->get(ActivityInterface::class);

        return $this->belongsTo($relation, 'last_activity_id');
    }

    /**
     * Find the most recent activity for this user of a particular type.
     *
     * @param string $type
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function lastActivityOfType($type = null)
    {
        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = static::$ci->classMapper;

        $query = $this->hasOne($classMapper->getClassMapping('activity'), 'user_id');

        if ($type) {
            $query = $query->where('type', $type);
        }

        return $query->latest('occurred_at');
    }

    /**
     * Get the most recent time for a specified activity type for this user.
     *
     * @param string $type
     *
     * @return string|null The last activity time, as a SQL formatted time (YYYY-MM-DD HH:MM:SS), or null if an activity of this type doesn't exist.
     */
    public function lastActivityTime($type)
    {
        $result = $this->activities()
            ->where('type', $type)
            ->max('occurred_at');

        return $result ? $result : null;
    }

    /**
     * Performs tasks to be done after this user has been successfully authenticated.
     *
     * By default, adds a new sign-in activity and updates any legacy hash.
     *
     * @param mixed[] $params Optional array of parameters used for this event handler.
     *
     * @todo Transition to Laravel Event dispatcher to handle this
     */
    public function onLogin($params = [])
    {
        // Add a sign in activity (time is automatically set by database)
        static::$ci->userActivityLogger->info("User {$this->user_name} signed in.", [
            'type' => 'sign_in',
        ]);

        // Update password if we had encountered an outdated hash
        $passwordType = Password::getHashType($this->password);

        if ($passwordType != 'modern') {
            if (!isset($params['password'])) {
                Debug::notice('Notice: Unhashed password must be supplied to update to modern password hashing.');
            } else {
                // Hash the user's password and update
                $passwordHash = Password::hash($params['password']);
                if ($passwordHash === null) {
                    Debug::notice('Notice: outdated password hash could not be updated because the new hashing algorithm is not supported.');
                } else {
                    $this->password = $passwordHash;
                    Debug::notice('Notice: outdated password hash has been automatically updated to modern hashing.');
                }
            }
        }

        // Save changes
        $this->save();

        return $this;
    }

    /**
     * Performs tasks to be done after this user has been logged out.
     *
     * By default, adds a new sign-out activity.
     *
     * @param mixed[] $params Optional array of parameters used for this event handler.
     *
     * @todo Transition to Laravel Event dispatcher to handle this
     */
    public function onLogout($params = [])
    {
        static::$ci->userActivityLogger->info("User {$this->user_name} signed out.", [
            'type' => 'sign_out',
        ]);

        return $this;
    }

    /**
     * Get all password reset requests for this user.
     *
     * @return PasswordResetInterface|HasMany
     */
    public function passwordResets()
    {
        /** @var string */
        $relation = static::$ci->get(PasswordResetInterface::class);

        return $this->hasMany($relation, 'user_id');
    }

    /**
     * Get all of the permissions this user has, via its roles.
     *
     * @return PermissionInterface|BelongsToManyThrough
     */
    public function permissions()
    {
        /** @var string */
        $permissionRelation = static::$ci->get(PermissionInterface::class);

        /** @var string */
        $roleRelation = static::$ci->get(RoleInterface::class);

        return $this->belongsToManyThrough(
            $permissionRelation,
            $roleRelation,
            'role_users',
            'user_id',
            'role_id',
            'permission_roles',
            'role_id',
            'permission_id'
        );
    }

    /**
     * Get all roles to which this user belongs.
     *
     * @return RoleInterface|BelongsToMany
     */
    public function roles()
    {
        /** @var string */
        $relation = static::$ci->get(RoleInterface::class);

        return $this->belongsToMany($relation, 'role_users', 'user_id', 'role_id')->withTimestamps();
    }

    /**
     * Query scope to get all users who have a specific role.
     *
     * @param Builder $query
     * @param int     $roleId
     *
     * @return Builder
     */
    public function scopeForRole($query, $roleId)
    {
        return $query->join('role_users', function ($join) use ($roleId) {
            $join->on('role_users.user_id', 'users.id')
                 ->where('role_id', $roleId);
        });
    }

    /**
     * Joins the user's most recent activity directly, so we can do things like sort, search, paginate, etc.
     *
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeJoinLastActivity($query)
    {
        $query = $query->select('users.*');

        $query = $query->leftJoin('activities', 'activities.id', '=', 'users.last_activity_id');

        return $query;
    }

    /**
     * Loads permissions for this user into a cached dictionary of slugs -> arrays of permissions,
     * so we don't need to keep requerying the DB for every call of checkAccess.
     *
     * @return array
     */
    protected function buildPermissionsDictionary()
    {
        $permissions = $this->permissions()->get();
        $cachedPermissions = [];

        foreach ($permissions as $permission) {
            $cachedPermissions[$permission->slug][] = $permission;
        }

        return $cachedPermissions;
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
