<?php

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin;

use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Admin\Routes\ActivitiesRoutes;
use UserFrosting\Sprinkle\Admin\Routes\ConfigRoutes;
use UserFrosting\Sprinkle\Admin\Routes\DashboardRoutes;
use UserFrosting\Sprinkle\Admin\Routes\GroupsRoute;
use UserFrosting\Sprinkle\Admin\Routes\PermissionsRoutes;
use UserFrosting\Sprinkle\Admin\Routes\RolesRoutes;
use UserFrosting\Sprinkle\Admin\Routes\UsersRoutes;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\SprinkleRecipe;

class Admin implements SprinkleRecipe
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'Admin Sprinkle';
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
     */
    public function getSprinkles(): array
    {
        return [
            Core::class,
            Account::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutes(): array
    {
        return [
            ActivitiesRoutes::class,
            DashboardRoutes::class,
            GroupsRoute::class,
            PermissionsRoutes::class,
            RolesRoutes::class,
            UsersRoutes::class,
            ConfigRoutes::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getServices(): array
    {
        return [];
    }
}
