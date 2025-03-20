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

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Seeder\SeedInterface;

/**
 * Seeder for the default permissions.
 */
class UpdatePermissions implements SeedInterface
{
    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        /** @var Role|null */
        $roleSiteAdmin = Role::where('slug', 'site-admin')->first();

        // create_user_field - Remove this role, will be replaced by group management permissions in 6.1
        Permission::where([
            'slug'       => 'create_user_field',
            'conditions' => "subset(fields,['group'])"
        ])->delete();

        // delete_user - Hard coded the master user ID verification, give up the role : Change to always()
        Permission::where([
            'slug'        => 'delete_user',
            'description' => 'Delete users who are not Site Administrators.'
        ])->update([
            'conditions'  => 'always()',
            'description' => 'Delete users.'
        ]);

        // update_user_field - It gives access to everything, so it should be changed to `always()` and have granular permissions in place in 6.1 (enable, etc.)
        Permission::where([
            'slug'        => 'update_user_field',
            'description' => 'Edit users who are not Site Administrators.'
        ])->update([
            'conditions'  => 'always()',
            'description' => 'Edit users.'
        ]);

        // update_user_field_group - Remove this role, will be replaced by group management permissions in 6.1
        Permission::where([
            'slug'        => 'update_user_field',
            'description' => 'Edit users in your own group who are not Site or Group Administrators, except yourself.',
        ])->delete();

        // update_user_field_role - Rename to `update_user_role`
        Permission::where([
            'slug'        => 'update_user_field',
            'description' => "Edit user's roles."
        ])->update([
            'slug'       => 'update_user_role',
            'conditions' => 'always()',
        ]);

        // update_role_field - It gives access to everything, so it should be changed to `always()` and have granular permissions in place in 6.1 (for permissions)
        Permission::where([
            'slug'        => 'update_role_field',
            'description' => 'Edit basic properties of any role.'
        ])->update([
            'conditions' => 'always()',
        ]);

        // uri_group_own - Rename SLUG to uri_group_own
        Permission::where([
            'slug'        => 'uri_group',
            'description' => 'View the group page of your own group.'
        ])->update([
            'slug'       => 'uri_group_own',
            'conditions' => 'always()',
        ]);

        // uri_user_in_group - Rename SLUG to uri_group_in_group
        Permission::where([
            'slug'        => 'uri_user',
            'description' => 'View the user page of any user in your group, except the master user and Site and Group Administrators (except yourself).'
        ])->update([
            'slug'        => 'uri_user_in_group',
            'conditions'  => 'always()',
            'description' => 'View the user page of any user in your group.'
        ]);

        // view_group_field - It gives access to everything, so it should be changed to `always()` and have granular permissions in place in 6.1 (for users)
        Permission::where([
            'slug'        => 'view_group_field',
            'description' => 'View certain properties of any group.'
        ])->update([
            'conditions'  => 'always()',
            'description' => 'View properties of any group.'
        ]);

        // view_group_field_own - It gives access to everything, so it should be changed to `always()`. Own group check will be done in code.
        Permission::where([
            'slug'        => 'view_group_field',
            'description' => 'View certain properties of your own group.'
        ])->update([
            'slug'        => 'view_group_field_own',
            'conditions'  => 'always()',
            'description' => 'View properties of your own group.'
        ]);

        // view_role_field - It gives access to everything, so it should be changed to `always()` and have granular permissions in place in 6.1 (for permissions & user)
        Permission::where([
            'slug'        => 'view_role_field',
            'description' => 'View certain properties of any role.'
        ])->update([
            'conditions'  => 'always()',
            'description' => 'View properties of any role.'
        ]);

        // view_user_field - It gives access to everything, so it should be changed to `always()` and have granular permissions in place in 6.1 (roles, group, activities. etc.)
        Permission::where([
            'slug'        => 'view_user_field',
            'description' => 'View certain properties of any user.'
        ])->update([
            'conditions'  => 'always()',
            'description' => 'View properties of any user.'
        ]);

        // view_user_field_permissions - Change to user permissions
        Permission::where([
            'slug'        => 'view_user_field',
            'name'        => "View user's permissions",
        ])->update([
            'slug'        => 'view_user_permissions',
            'conditions'  => 'always()',
            'description' => 'View permissions of any user.'
        ]);

        // view_user_field_group - DELETE
        Permission::where([
            'slug'        => 'view_user_field',
            'description' => 'View certain properties of any user in your own group, except the master user and Site and Group Administrators (except yourself).'
        ])->delete();

        // ADD - view_user_activities
        $view_user_activities = new Permission([
            'slug'        => 'view_user_activities',
            'name'        => "View user's Activities",
            'conditions'  => 'always()',
            'description' => 'View activities of any user.',
        ]);
        /** @var Permission|null */
        $existingPermission = Permission::where([
            'slug'        => $view_user_activities->slug,
            'name'        => $view_user_activities->name,
            'conditions'  => $view_user_activities->conditions,
            'description' => $view_user_activities->description
        ])->first();
        if ($existingPermission === null) {
            $view_user_activities->save();
            $roleSiteAdmin?->permissions()->syncWithoutDetaching($view_user_activities);
        } else {
            $roleSiteAdmin?->permissions()->syncWithoutDetaching($existingPermission);
        }

        // ADD - view_user_roles
        // Trying to find if the permission already exists
        $view_user_roles = new Permission([
            'slug'        => 'view_user_roles',
            'name'        => "View user's Roles",
            'conditions'  => 'always()',
            'description' => 'View roles of any user.',
        ]);
        /** @var Permission|null */
        $existingPermission = Permission::where([
            'slug'        => $view_user_roles->slug,
            'name'        => $view_user_roles->name,
            'conditions'  => $view_user_roles->conditions,
            'description' => $view_user_roles->description
        ])->first();
        if ($existingPermission === null) {
            $view_user_roles->save();
            $roleSiteAdmin?->permissions()->syncWithoutDetaching($view_user_roles);
        } else {
            $roleSiteAdmin?->permissions()->syncWithoutDetaching($existingPermission);
        }
    }
}
