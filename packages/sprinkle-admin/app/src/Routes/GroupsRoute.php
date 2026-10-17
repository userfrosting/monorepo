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
use UserFrosting\Sprinkle\Admin\Controller\Group\GroupActivitySprunje;
use UserFrosting\Sprinkle\Admin\Controller\Group\GroupApi;
use UserFrosting\Sprinkle\Admin\Controller\Group\GroupCreateAction;
use UserFrosting\Sprinkle\Admin\Controller\Group\GroupDeleteAction;
use UserFrosting\Sprinkle\Admin\Controller\Group\GroupEditAction;
use UserFrosting\Sprinkle\Admin\Controller\Group\GroupsSprunjeAction;
use UserFrosting\Sprinkle\Admin\Controller\Group\GroupUsersSprunje;
use UserFrosting\Sprinkle\Admin\Middlewares\GroupInjector;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;

/*
 * Routes for administrative group management.
 */
class GroupsRoute implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/groups', function (RouteCollectorProxy $group) {
            $group->get('', GroupsSprunjeAction::class)
                  ->setName('api_groups');
            $group->get('/g/{slug}', GroupApi::class)
                  ->add(GroupInjector::class)
                  ->setName('api_group');
            $group->delete('/g/{slug}', GroupDeleteAction::class)
                  ->add(GroupInjector::class);
            $group->get('/g/{slug}/users', GroupUsersSprunje::class)
                  ->add(GroupInjector::class);
            $group->get('/g/{slug}/activities', GroupActivitySprunje::class)
                  ->add(GroupInjector::class);
            $group->post('', GroupCreateAction::class);
            $group->put('/g/{slug}', GroupEditAction::class)
                  ->add(GroupInjector::class);
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
