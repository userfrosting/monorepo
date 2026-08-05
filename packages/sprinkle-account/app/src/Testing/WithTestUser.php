<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Testing;

use UserFrosting\Config\Config;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\PermissionInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;

trait WithTestUser
{
    /**
     * Set user for tests.
     *
     * @param UserInterface                  $user
     * @param bool                           $isMaster    If true, will set user as master user (permission for everything).
     * @param RoleInterface[]                $roles
     * @param (PermissionInterface|string)[] $permissions Permission will be added through a new empty role.
     */
    protected function actAsUser(
        UserInterface $user,
        bool $isMaster = false,
        array $roles = [],
        array $permissions = []
    ): void {
        /** @var Config */
        $config = $this->getService(Config::class);
        $masterId = ($isMaster) ? $user->id : 0;
        $config->set('reserved_user_ids.master', $masterId);

        /** @var Authenticator */
        $authenticator = $this->getService(Authenticator::class);
        $authenticator->login($user);

        // Assign roles
        foreach ($roles as $role) {
            $user->roles()->attach($role);
            $user->save();
        }

        // Assign permissions
        if (count($permissions) !== 0) {
            /** @var Role */
            $role = Role::factory()->create();
            $user->roles()->attach($role);

            foreach ($permissions as $permission) {
                if (is_string($permission)) {
                    $permission = new Permission([
                        'slug'       => $permission,
                        'name'       => $permission,
                        'conditions' => 'always()',
                    ]);
                    $permission->save();
                }
                $role->permissions()->attach($permission);
                $role->save();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function createApplication(): void
    {
        parent::createApplication();

        // Make sure we have a session
        /** @var Session */
        $session = $this->getService(Session::class);
        $session->start();
    }

    /**
     * {@inheritDoc}
     */
    protected function deleteApplication(): void
    {
        // Make sure to clean up the session before we delete the application.
        /** @var Session */
        $session = $this->getService(Session::class);
        $session->destroy();

        parent::deleteApplication();
    }
}
