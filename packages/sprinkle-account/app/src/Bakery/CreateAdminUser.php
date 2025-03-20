<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Bakery;

use DI\Attribute\Inject;
use Symfony\Component\Console\Command\Command;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Bakery\Exception\BakeryNote;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Seeds\DefaultGroups;
use UserFrosting\Sprinkle\Account\Database\Seeds\DefaultPermissions;
use UserFrosting\Sprinkle\Account\Database\Seeds\DefaultRoles;
use UserFrosting\Sprinkle\Account\Database\Seeds\UpdatePermissions;

/**
 * Create root user CLI command. Same as CreateUser, but will abort if the root user already exists.
 * This allows to add this command to the bake command.
 */
class CreateAdminUser extends CreateUser
{
    /** @var string The command name */
    protected string $commandName = 'create:admin-user';

    /** @var string The command name */
    protected string $commandTitle = 'Creating new admin (root) user';

    #[Inject]
    protected DefaultGroups $defaultGroups;

    #[Inject]
    protected DefaultPermissions $defaultPermissions;

    #[Inject]
    protected UpdatePermissions $updatePermissions;

    #[Inject]
    protected DefaultRoles $defaultRoles;

    /**
     * {@inheritdoc}
     */
    protected function validateRequirements(): void
    {
        parent::validateRequirements();

        // Make sure that there are no users currently in the user table
        // We setup the root account here so it can be done independent of the version check
        // TODO : We should get the id from config.
        if (User::count() > 0) {
            throw new BakeryNote("Table 'users' is not empty. Skipping root account setup.");
        }

        // Run seeds if needed
        if (Group::count() === 0) {
            $this->io->note('Running default groups seed...');
            $this->defaultGroups->run();
        }
        if (Role::count() === 0) {
            $this->io->note('Running default roles seed...');
            $this->defaultRoles->run();
        }
        if (Permission::count() === 0) {
            $this->io->note('Running default permissions seed...');
            $this->defaultPermissions->run();
            $this->updatePermissions->run();
        }
    }
}
