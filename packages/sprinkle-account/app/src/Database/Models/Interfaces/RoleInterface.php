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

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use UserFrosting\Sprinkle\Core\Database\Models\Model;

/**
 * Role Model Interface.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \UserFrosting\Sprinkle\Core\Database\Models\Model
 *
 * @property int                                        $id
 * @property string                                     $slug
 * @property string                                     $name
 * @property string                                     $description
 * @property Collection<int, UserInterface&Model>       $users
 * @property Collection<int, PermissionInterface&Model> $permissions
 *
 * @method        $this forUser(int|UserInterface $user)
 * @method static $this forUser(int|UserInterface $user)
 */
interface RoleInterface
{
    /**
     * Get a list of permissions assigned to this role.
     *
     * @return BelongsToMany
     */
    public function permissions(): BelongsToMany;

    /**
     * Get a list of users who have this role.
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany;
}
