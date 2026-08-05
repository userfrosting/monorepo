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

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder as QueryBuilder;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough;

/**
 * Permission Model Interface.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \UserFrosting\Sprinkle\Core\Database\Models\Model
 *
 * @property int                                  $id
 * @property string                               $slug
 * @property string                               $name
 * @property string                               $conditions
 * @property string                               $description
 * @property Collection<int, UserInterface&Model> $users
 * @property Collection<int, RoleInterface&Model> $roles
 *
 * @method        $this forRole(int|RoleInterface $role)
 * @method static $this forRole(int|RoleInterface $role)
 * @method        $this notForRole(int|RoleInterface $role)
 * @method static $this notForRole(int|RoleInterface $role)
 */
interface PermissionInterface
{
    /**
     * Get a list of roles to which this permission is assigned.
     *
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany;

    /**
     * Get a list of users who have this permission, along with a list of roles through which each user has the permission.
     *
     * @return BelongsToManyThrough
     */
    public function users(): BelongsToManyThrough;

    /**
     * Query scope to get all permissions assigned to a specific role.
     *
     * @param Builder           $query
     * @param int|RoleInterface $role  Role Model or Role ID
     *
     * @return Builder|QueryBuilder
     */
    public function scopeForRole(Builder $query, int|RoleInterface $role): Builder|QueryBuilder;

    /**
     * Query scope to get all permissions NOT associated with a specific role.
     *
     * @param Builder           $query
     * @param int|RoleInterface $role  Role Model or Role ID
     *
     * @return Builder|QueryBuilder
     */
    public function scopeNotForRole(Builder $query, int|RoleInterface $role): Builder|QueryBuilder;
}
