<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Seeds;

use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

/**
 * Seeder for the default roles.
 */
class DefaultRoles implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        $roles = $this->getRoles();

        foreach ($roles as $role) {
            // Don't save if already exist
            if (Role::where('slug', $role->slug)->first() === null) {
                $role->save();
            }
        }
    }

    /**
     * @return Role[] Roles to seed
     */
    protected function getRoles(): array
    {
        return [
            new Role([
                'slug'        => 'user',
                'name'        => 'User',
                'description' => 'This role provides basic user functionality.',
            ]),
            new Role([
                'slug'        => 'site-admin',
                'name'        => 'Site Administrator',
                'description' => 'This role is meant for "site administrators", who can basically do anything except create, edit, or delete other administrators.',
            ]),
            new Role([
                'slug'        => 'group-admin',
                'name'        => 'Group Administrator',
                'description' => 'This role is meant for "group administrators", who can basically do anything with users in their own group, except other administrators of that group.',
            ]),
        ];
    }
}
