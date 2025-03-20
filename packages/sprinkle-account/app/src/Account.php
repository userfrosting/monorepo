<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account;

use UserFrosting\Event\EventListenerRecipe;
use UserFrosting\Sprinkle\Account\Bakery\BakeCommandListener;
use UserFrosting\Sprinkle\Account\Bakery\CreateAdminUser;
use UserFrosting\Sprinkle\Account\Bakery\CreateUser;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\ActivitiesTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\GroupsTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PasswordResetsTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionRolesTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionsTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\PersistencesTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\RoleUsersTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\VerificationsTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v420\AddingForeignKeys;
use UserFrosting\Sprinkle\Account\Database\Migrations\v430\UpdateGroupsTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v430\UpdateUsersTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v500\UpdateUsersTable as V500UpdateUsersTable;
use UserFrosting\Sprinkle\Account\Database\Seeds\DefaultGroups;
use UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions;
use UserFrosting\Sprinkle\Account\Database\Seeds\DefaultRoles;
use UserFrosting\Sprinkle\Account\Database\Seeds\UpdatePermissions;
use UserFrosting\Sprinkle\Account\Event\UserAuthenticatedEvent;
use UserFrosting\Sprinkle\Account\Event\UserCreatedEvent;
use UserFrosting\Sprinkle\Account\Event\UserLoggedInEvent;
use UserFrosting\Sprinkle\Account\Event\UserLoggedOutEvent;
use UserFrosting\Sprinkle\Account\Listener\AssignDefaultGroups;
use UserFrosting\Sprinkle\Account\Listener\AssignDefaultRoles;
use UserFrosting\Sprinkle\Account\Listener\UpgradePassword;
use UserFrosting\Sprinkle\Account\Listener\UserLogoutActivity;
use UserFrosting\Sprinkle\Account\Listener\UserSignInActivity;
use UserFrosting\Sprinkle\Account\Routes\AuthRoutes;
use UserFrosting\Sprinkle\Account\ServicesProvider\AccessConditionsService;
use UserFrosting\Sprinkle\Account\ServicesProvider\AuthorizationService;
use UserFrosting\Sprinkle\Account\ServicesProvider\AuthService;
use UserFrosting\Sprinkle\Account\ServicesProvider\I18nService;
use UserFrosting\Sprinkle\Account\ServicesProvider\LoggersService;
use UserFrosting\Sprinkle\Account\ServicesProvider\ModelsService;
use UserFrosting\Sprinkle\Account\Twig\AccountExtension;
use UserFrosting\Sprinkle\BakeryRecipe;
use UserFrosting\Sprinkle\Core\Bakery\Event\BakeCommandEvent;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MigrationRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\SeedRecipe;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\TwigExtensionRecipe;
use UserFrosting\Sprinkle\SprinkleRecipe;

class Account implements
    SprinkleRecipe,
    MigrationRecipe,
    SeedRecipe,
    EventListenerRecipe,
    TwigExtensionRecipe,
    BakeryRecipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Account Sprinkle';
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(): string
    {
        return __DIR__ . '/../';
    }

    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function getBakeryCommands(): array
    {
        return [
            CreateAdminUser::class,
            CreateUser::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSprinkles(): array
    {
        return [
            Core::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutes(): array
    {
        return [
            AuthRoutes::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return [
            AccessConditionsService::class,
            AuthorizationService::class,
            AuthService::class,
            ModelsService::class,
            I18nService::class,
            LoggersService::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getMigrations(): array
    {
        return [
            // v400
            ActivitiesTable::class,
            GroupsTable::class,
            PasswordResetsTable::class,
            PermissionRolesTable::class,
            RolesTable::class,
            PermissionsTable::class,
            PersistencesTable::class,
            RoleUsersTable::class,
            UsersTable::class,
            // v420
            VerificationsTable::class,
            AddingForeignKeys::class,
            // v430
            UpdateGroupsTable::class,
            UpdateUsersTable::class,
            // v500
            V500UpdateUsersTable::class,
        ];
    }

    /**
     * {@inheritDoc}
     *
     * @codeCoverageIgnore
     */
    public function getSeeds(): array
    {
        return [
            DefaultGroups::class,
            DefaultPermissions::class,
            DefaultRoles::class,
            UpdatePermissions::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getEventListeners(): array
    {
        return [
            BakeCommandEvent::class       => [
                BakeCommandListener::class,
            ],
            UserCreatedEvent::class       => [
                AssignDefaultRoles::class,
                AssignDefaultGroups::class,
            ],
            UserLoggedInEvent::class      => [
                UserSignInActivity::class,
            ],
            UserLoggedOutEvent::class     => [
                UserLogoutActivity::class,
            ],
            UserAuthenticatedEvent::class => [
                UpgradePassword::class,
            ],
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getTwigExtensions(): array
    {
        return [
            AccountExtension::class,
        ];
    }
}
