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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\Builder as QueryBuilder;
use UserFrosting\Sprinkle\Account\Database\Factories\RoleFactory;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PermissionInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Role Class.
 *
 * Represents a role, which aggregates permissions and to which a user can be assigned.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Role extends Model implements RoleInterface
{
    use SoftDeletes;
    use HasFactory;

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'roles';

    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'deleted_at',
    ];

    /**
     * @var string[] The attributes that should be mutated to dates.
     */
    protected $dates = [
        'deleted_at',
    ];

    /**
     * Cast nullable description to empty string if null.
     *
     * @param string|null $value
     *
     * @return string
     */
    public function getDescriptionAttribute(?string $value): string
    {
        return $value ?? '';
    }

    /**
     * Delete this role from the database, removing associations with permissions and users.
     */
    public function delete()
    {
        // Remove all permission associations
        $this->permissions()->detach();

        // Remove all user associations
        $this->users()->detach();

        // Delete the role
        return parent::delete();
    }

    /**
     * Get a list of permissions assigned to this role.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        /** @var string */
        $relation = static::$ci?->get(PermissionInterface::class);

        return $this->belongsToMany($relation, 'permission_roles', 'role_id', 'permission_id')->withTimestamps();
    }

    /**
     * Query scope to get all roles assigned to a specific user.
     *
     * @param Builder           $query
     * @param int|UserInterface $user
     *
     * @return Builder|QueryBuilder
     */
    public function scopeForUser(Builder $query, int|UserInterface $user): Builder|QueryBuilder
    {
        if ($user instanceof UserInterface) {
            $userId = $user->id;
        } else {
            $userId = $user;
        }

        return $query->whereHas('users', function ($q) use ($userId) {
            $q->where('users.id', $userId);
        });
    }

    /**
     * Get a list of users who have this role.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        /** @var string */
        $relation = static::$ci?->get(UserInterface::class);

        return $this->belongsToMany($relation, 'role_users', relatedPivotKey: 'user_id');
    }

    /**
     * Create a new factory instance for the model.
     *
     * @return Factory<Role>
     */
    protected static function newFactory(): Factory
    {
        return RoleFactory::new();
    }
}
