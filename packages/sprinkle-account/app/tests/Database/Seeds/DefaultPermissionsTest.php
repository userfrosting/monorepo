<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Database\Models;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions;
use UserFrosting\Sprinkle\Account\Database\Seeds\UpdatePermissions;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * DefaultPermissions Seed Test.
 */
class DefaultPermissionsTest extends AccountTestCase
{
    use RefreshDatabase;

    /**
     * Test the default seed.
     */
    public function testSeed(): void
    {
        // Setup fresh, empty table
        $this->refreshDatabase();

        /** @var Permission */
        $permission = $this->ci->get(Permission::class);

        // Assert initial table state
        $this->assertCount(0, $permission::all());

        // Apply seed
        $seed = new DefaultPermissions();
        $seed->run();

        // Assert new table state
        $this->assertCount(33, Permission::all());

        // Test running again
        $seed->run();
        $this->assertCount(33, Permission::all());
    }

    /**
     * Test the updated permissions once we've run the update seed.
     */
    public function testUpdateSeed(): void
    {
        // Setup fresh, empty table
        $this->refreshDatabase();

        /** @var Permission */
        $permission = $this->ci->get(Permission::class);

        // Assert initial table state
        $this->assertCount(0, $permission::all());

        // Apply seeds
        $seed = new DefaultPermissions();
        $seed->run();
        $seed = new UpdatePermissions();
        $seed->run();

        // Assert new table state
        $permissions = Permission::orderBy('slug')->get();
        $this->assertCount(32, $permissions);

        // Assert permissions
        // @phpstan-ignore-next-line
        $this->assertSame($this->permissions, $permissions->setVisible([
            'slug',
            'name',
            'conditions',
            'description'
        ])->toArray());

        // Test running again
        $seed->run();
        $this->assertCount(32, Permission::all());
    }

    /**
     * Undocumented variable
     *
     * @var mixed[]
     */
    protected array $permissions = [
        [
            'slug'        => 'clear_cache',
            'name'        => 'Clear system cache',
            'conditions'  => 'always()',
            'description' => 'Clear the system cache from the administrative dashboard.',
        ],
        [
            'slug'        => 'create_group',
            'name'        => 'Create group',
            'conditions'  => 'always()',
            'description' => 'Create a new group.',
        ],
        [
            'slug'        => 'create_role',
            'name'        => 'Create role',
            'conditions'  => 'always()',
            'description' => 'Create a new role.',
        ],
        [
            'slug'        => 'create_user',
            'name'        => 'Create user',
            'conditions'  => 'always()',
            'description' => 'Create a new user in your own group and assign default roles.',
        ],
        [
            'slug'        => 'delete_group',
            'name'        => 'Delete group',
            'conditions'  => 'always()',
            'description' => 'Delete a group.',
        ],
        [
            'slug'        => 'delete_role',
            'name'        => 'Delete role',
            'conditions'  => 'always()',
            'description' => 'Delete a role.',
        ],
        [
            'slug'        => 'delete_user',
            'name'        => 'Delete user',
            'conditions'  => 'always()',
            'description' => 'Delete users.',
        ],
        [
            'slug'        => 'update_account_settings',
            'name'        => 'Edit user',
            'conditions'  => 'always()',
            'description' => 'Edit your own account settings.',
        ],
        [
            'slug'        => 'update_group_field',
            'name'        => 'Edit group',
            'conditions'  => 'always()',
            'description' => 'Edit basic properties of any group.',
        ],
        [
            'slug'        => 'update_role_field',
            'name'        => 'Edit role',
            'conditions'  => 'always()',
            'description' => 'Edit basic properties of any role.',
        ],
        [
            'slug'        => 'update_user_field',
            'name'        => 'Edit user',
            'conditions'  => 'always()',
            'description' => 'Edit users.',
        ],
        [
            'slug'        => 'update_user_role',
            'name'        => "Edit user's role",
            'conditions'  => 'always()',
            'description' => "Edit user's roles.",
        ],
        [
            'slug'        => 'uri_account_settings',
            'name'        => 'Account settings page',
            'conditions'  => 'always()',
            'description' => 'View the account settings page.',
        ],
        [
            'slug'        => 'uri_activities',
            'name'        => 'Activity monitor',
            'conditions'  => 'always()',
            'description' => 'View a list of all activities for all users.',
        ],
        [
            'slug'        => 'uri_dashboard',
            'name'        => 'Admin dashboard',
            'conditions'  => 'always()',
            'description' => 'View the administrative dashboard.',
        ],
        [
            'slug'        => 'uri_group',
            'name'        => 'View group',
            'conditions'  => 'always()',
            'description' => 'View the group page of any group.',
        ],
        [
            'slug'        => 'uri_group_own',
            'name'        => 'View own group',
            'conditions'  => 'always()',
            'description' => 'View the group page of your own group.',
        ],
        [
            'slug'        => 'uri_groups',
            'name'        => 'Group management page',
            'conditions'  => 'always()',
            'description' => 'View a page containing a list of groups.',
        ],
        [
            'slug'        => 'uri_permissions',
            'name'        => 'Permission management page',
            'conditions'  => 'always()',
            'description' => 'View a page containing a list of permissions.',
        ],
        [
            'slug'        => 'uri_role',
            'name'        => 'View role',
            'conditions'  => 'always()',
            'description' => 'View the role page of any role.',
        ],
        [
            'slug'        => 'uri_roles',
            'name'        => 'Role management page',
            'conditions'  => 'always()',
            'description' => 'View a page containing a list of roles.',
        ],
        [
            'slug'        => 'uri_user',
            'name'        => 'View user',
            'conditions'  => 'always()',
            'description' => 'View the user page of any user.',
        ],
        [
            'slug'        => 'uri_user_in_group',
            'name'        => 'View user',
            'conditions'  => 'always()',
            'description' => 'View the user page of any user in your group.',
        ],
        [
            'slug'        => 'uri_users',
            'name'        => 'User management page',
            'conditions'  => 'always()',
            'description' => 'View a page containing a table of users.',
        ],
        [
            'slug'        => 'view_group_field',
            'name'        => 'View group',
            'conditions'  => 'always()',
            'description' => 'View properties of any group.',
        ],
        [
            'slug'        => 'view_group_field_own',
            'name'        => 'View group',
            'conditions'  => 'always()',
            'description' => 'View properties of your own group.',
        ],
        [
            'slug'        => 'view_role_field',
            'name'        => 'View role',
            'conditions'  => 'always()',
            'description' => 'View properties of any role.',
        ],
        [
            'slug'        => 'view_system_info',
            'name'        => 'View system info',
            'conditions'  => 'always()',
            'description' => 'View the system information in the administrative dashboard.',
        ],
        [
            'slug'        => 'view_user_activities',
            'name'        => "View user's Activities",
            'conditions'  => 'always()',
            'description' => 'View activities of any user.',
        ],
        [
            'slug'        => 'view_user_field',
            'name'        => 'View user',
            'conditions'  => 'always()',
            'description' => 'View properties of any user.',
        ],
        [
            'slug'        => 'view_user_permissions',
            'name'        => "View user's permissions",
            'conditions'  => 'always()',
            'description' => 'View permissions of any user.',
        ],
        [
            'slug'        => 'view_user_roles',
            'name'        => "View user's Roles",
            'conditions'  => 'always()',
            'description' => 'View roles of any user.',
        ],
    ];
}
