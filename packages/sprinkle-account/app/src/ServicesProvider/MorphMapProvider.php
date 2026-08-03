<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\ServicesProvider;

use Illuminate\Database\Eloquent\Relations\Relation;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Persistence;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\UserVerification;

/**
 * Registers Eloquent polymorphic type aliases.
 *
 * Storing short aliases instead of fully-qualified class names in the
 * activity_log table keeps the data portable and decoupled from the
 * class hierarchy.
 */
class MorphMapProvider implements ServicesProviderInterface
{
    public function register(): array
    {
        Relation::morphMap([
            'activity'          => Activity::class,
            'group'             => Group::class,
            'permission'        => Permission::class,
            'persistence'       => Persistence::class,
            'role'              => Role::class,
            'user'              => User::class,
            'user_verification' => UserVerification::class,
        ]);

        return [];
    }
}
