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

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Activity;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PermissionInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PersistenceInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserVerificationInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Persistence;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\UserVerification;

/**
 * Map models interface to the class.
 *
 * Note both class are map using class-string, since Models are not instantiated
 * by the container in the Eloquent world.
 */
class ModelsService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            ActivityInterface::class           => \DI\autowire(Activity::class),
            GroupInterface::class              => \DI\autowire(Group::class),
            PermissionInterface::class         => \DI\autowire(Permission::class),
            PersistenceInterface::class        => \DI\autowire(Persistence::class),
            RoleInterface::class               => \DI\autowire(Role::class),
            UserInterface::class               => \DI\autowire(User::class),
            UserVerificationInterface::class   => \DI\autowire(UserVerification::class),
        ];
    }
}
