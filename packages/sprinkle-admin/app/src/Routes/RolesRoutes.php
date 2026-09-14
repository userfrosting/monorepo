<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Routes;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Admin\Controller\Role\RoleApi;
use UserFrosting\Sprinkle\Admin\Controller\Role\RoleCreateAction;
use UserFrosting\Sprinkle\Admin\Controller\Role\RoleDeleteAction;
use UserFrosting\Sprinkle\Admin\Controller\Role\RoleEditAction;
use UserFrosting\Sprinkle\Admin\Controller\Role\RolePermissionsAction;
use UserFrosting\Sprinkle\Admin\Controller\Role\RolePermissionsSprunje;
use UserFrosting\Sprinkle\Admin\Controller\Role\RolesSprunje;
use UserFrosting\Sprinkle\Admin\Controller\Role\RoleUsersSprunje;
use UserFrosting\Sprinkle\Admin\Middlewares\RoleInjector;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;

/*
 * Routes for administrative role management.
 */
class RolesRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/roles', function (RouteCollectorProxy $group) {
            $group->get('/r/{slug}', RoleApi::class)
                  ->add(RoleInjector::class);
            $group->delete('/r/{slug}', RoleDeleteAction::class)
                  ->add(RoleInjector::class);
            $group->get('', RolesSprunje::class);
            $group->get('/r/{slug}/permissions', RolePermissionsSprunje::class)
                  ->add(RoleInjector::class);
            $group->get('/r/{slug}/users', RoleUsersSprunje::class)
                  ->add(RoleInjector::class);
            $group->post('', RoleCreateAction::class);
            $group->put('/r/{slug}', RoleEditAction::class)
                  ->add(RoleInjector::class);
            $group->put('/r/{slug}/permissions', RolePermissionsAction::class)
                  ->add(RoleInjector::class);
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
