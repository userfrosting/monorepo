<?php

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

/**
 * US English message token translations for the 'admin' sprinkle.
 *
 * @author Alexander Weissman
 */
return [
    'ACTIVITY' => [
        1 => 'Activity',
        2 => 'Activities',

        'LAST'             => 'Last Activity',
        'LATEST'           => 'Latest Activities',
        'PAGE'             => 'Activities',
        'PAGE_DESCRIPTION' => 'A listing of user activities',
        'TIME'             => 'Activity Time',
    ],
    'ADMIN_PANEL' => 'Admin Panel',

    'DASHBOARD'             => 'Dashboard',
    'DELETE_MASTER'         => 'You cannot delete the master account',
    'DELETION_SUCCESSFUL'   => 'User <strong>{{user_name}}</strong> has been successfully deleted.',
    'DETAILS_UPDATED'       => 'Account details updated for user <strong>{{user_name}}</strong>',
    'DISABLE_MASTER'        => 'You cannot disable the master account',
    'DISABLE_SELF'          => 'You cannot disable your own account',
    'DISABLE_SUCCESSFUL'    => 'Account for user <strong>{{user_name}}</strong> has been successfully disabled.',

    'ENABLE_SUCCESSFUL' => 'Account for user <strong>{{user_name}}</strong> has been successfully enabled.',

    'GROUP' => [
        1 => 'Group',
        2 => 'Groups',

        'CREATE'              => 'Create group',
        'CREATION_SUCCESSFUL' => 'Successfully created group <strong>{{name}}</strong>',
        'DELETE'              => 'Delete group',
        'DELETE_CONFIRM'      => 'Are you sure you want to delete the group <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => "You can't delete the group <strong>{{name}}</strong> because it is the default group for newly registered users.",
        'DELETE_YES'          => 'Yes, delete group',
        'DELETION_SUCCESSFUL' => 'Successfully deleted group <strong>{{name}}</strong>',
        'EDIT'                => 'Edit group',
        'EXCEPTION'           => 'Group error',
        'ICON'                => 'Group icon',
        'ICON_EXPLAIN'        => 'Icon for group members',
        'INFO_PAGE'           => 'View and edit group details.',
        'NAME'                => 'Group name',
        'NAME_IN_USE'         => 'A group named <strong>{{name}}</strong> already exist',
        'NAME_EXPLAIN'        => 'Please enter a name for the group',
        'NONE'                => 'No group',
        'NOT_EMPTY'           => "You can't do that because there are still users associated with the group <strong>{{name}}</strong>.",
        'NOT_FOUND'           => 'Group not found',
        'PAGE'                => 'Groups',
        'PAGE_DESCRIPTION'    => 'A listing of the groups for your site.  Provides management tools for editing and deleting groups.',
        'UPDATE'              => 'Details updated for group <strong>{{name}}</strong>',
        'USERS'               => 'Users in this group',
    ],

    'MANUALLY_ACTIVATED'    => "{{user_name}}'s account has been manually activated",

    'PERMISSION' => [
        1 => 'Permission',
        2 => 'Permissions',

        'ASSIGN'            => [
            '@TRANSLATION' => 'Assign permissions',
            'EXPLAIN'      => 'The selected permissions will be assigned to the role.',
        ],
        'HOOK_CONDITION'    => 'Hook/Conditions',
        'ID'                => 'Permission ID',
        'INFO_PAGE'         => 'View and edit permission details.',
        'NOT_FOUND'         => 'Permission not found',
        'PAGE'              => 'Permissions',
        'PAGE_DESCRIPTION'  => 'A listing of the permissions for your site.  Provides management tools for editing and deleting permissions.',
        'UPDATE'            => 'Update permissions',
        'USERS'             => 'Users with this permission',
        'VIA_ROLES'         => 'Has permission via roles',
    ],

    'ROLE' => [
        1 => 'Role',
        2 => 'Roles',

        'CREATE'              => 'Create role',
        'CREATION_SUCCESSFUL' => 'Successfully created role <strong>{{name}}</strong>',
        'DELETE'              => 'Delete role',
        'DELETE_CONFIRM'      => 'Are you sure you want to delete the role <strong>{{name}}</strong>?',
        'DELETE_DEFAULT'      => "You can't delete the role <strong>{{name}}</strong> because it is a default role for newly registered users.",
        'DELETE_YES'          => 'Yes, delete role',
        'DELETION_SUCCESSFUL' => 'Successfully deleted role <strong>{{name}}</strong>',
        'EDIT'                => 'Edit role',
        'EXCEPTION'           => 'Role error',
        'HAS_USERS'           => "You can't do that because there are still users who have the role <strong>{{name}}</strong>.",
        'INFO_PAGE'           => 'View and edit role details.',
        'MANAGE'              => 'Manage Roles',
        'MANAGE_EXPLAIN'      => 'The selected roles will be assigned to the user.',
        'NAME'                => 'Name',
        'NAME_EXPLAIN'        => 'Please enter a name for the role',
        'NAME_IN_USE'         => 'A role named <strong>{{name}}</strong> already exist',
        'NOT_FOUND'           => 'Role not found',
        'PAGE'                => 'Roles',
        'PAGE_DESCRIPTION'    => 'A listing of the roles for your site. Provides management tools for editing and deleting roles.',
        'PERMISSIONS'         => 'Role permissions',
        'PERMISSIONS_UPDATED' => 'Permissions updated for role <strong>{{name}}</strong>',
        'UPDATE'              => 'Update Roles',
        'UPDATED'             => 'Details updated for role <strong>{{name}}</strong>',
        'USERS'               => 'Users with this role',
    ],

    'SITE_CONFIG' => [
        '@TRANSLATION'      => 'Site configuration',
        'CACHE'             => [
            '@TRANSLATION'      => 'Cache Management',
            'CLEAR'             => 'Clear cache',
            'CLEAR_CONFIRM'     => 'Are you sure you want to clear the site cache?',
            'CLEAR_CONFIRM_YES' => 'Yes, clear cache',
            'CLEARED'           => 'Cache cleared successfully !',
        ],
        'PAGE_DESCRIPTION'  => 'Use the forms below to update your site configuration settings.',
        'SYSTEM_INFO'       => [
            '@TRANSLATION'  => 'System information',
            'DB_NAME'       => 'Database Name',
            'DB_CONNECTION' => 'Database Connection',
            'DB_VERSION'    => 'Database Version',
            'DIRECTORY'     => 'Project Directory',
            'PHP_VERSION'   => 'PHP Version',
            'SERVER'        => 'Webserver Software',
            'SPRINKLES'     => 'Loaded Sprinkles',
            'UF_VERSION'    => 'Framework Version',
        ],
    ],

    'USER' => [
        1 => 'User',
        2 => 'Users',

        'ADMIN' => [
            'CHANGE_PASSWORD'        => 'Change User\'s Password',
            'PASSWORD_RESET_SUCCESS' => '<strong>{{full_name}}</strong>\'s password has been reset.',
            'PASSWORD_RESET'         => 'Reset user\'s password',
            'PASSWORD_RESET_CONFIRM' => 'Are you sure you want to force reset <strong>{{full_name}}</strong>\'s password? Their current password will be deactivated and they will need to reset it using the "reset password" feature on their next login attempt.',
        ],
        'ACTIVATE'          => 'Activate user',
        'ACTIVATE_CONFIRM'  => 'Are you sure you want to activate <strong>{{full_name}} ({{user_name}})</strong> ?',
        'CREATE'            => 'Create user',
        'CREATED'           => 'User <strong>{{user_name}}</strong> has been successfully created',
        'DELETE'            => 'Delete user',
        'DELETE_CONFIRM'    => 'Are you sure you want to delete the user <strong>{{full_name}} ({{user_name}})</strong>?',
        'DELETED'           => 'User deleted',
        'DISABLE'           => 'Disable user',
        'DISABLE_CONFIRM'   => 'Are you sure you want to disable <strong>{{full_name}} ({{user_name}})</strong> ?',
        'EDIT'              => 'Edit user',
        'ENABLE'            => 'Enable user',
        'ENABLE_CONFIRM'    => 'Are you sure you want to enable <strong>{{full_name}} ({{user_name}})</strong> ?',
        'INFO_PAGE'         => 'View and edit user details.',
        'LATEST'            => 'Latest Users',
        'PAGE'              => 'Users',
        'PAGE_DESCRIPTION'  => 'A listing of the users for your site. Provides management tools including the ability to edit user details, manually activate users, enable/disable users, and more.',
        'VIEW_ALL'          => 'View all users',
    ],
];
