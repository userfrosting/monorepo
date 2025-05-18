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
use UserFrosting\Sprinkle\Admin\Controller\User\UserActivitySprunje;
use UserFrosting\Sprinkle\Admin\Controller\User\UserApi;
use UserFrosting\Sprinkle\Admin\Controller\User\UserCreateAction;
use UserFrosting\Sprinkle\Admin\Controller\User\UserDeleteAction;
use UserFrosting\Sprinkle\Admin\Controller\User\UserEditAction;
use UserFrosting\Sprinkle\Admin\Controller\User\UserPasswordResetAction;
use UserFrosting\Sprinkle\Admin\Controller\User\UserPermissionSprunje;
use UserFrosting\Sprinkle\Admin\Controller\User\UserRoleSprunje;
use UserFrosting\Sprinkle\Admin\Controller\User\UsersSprunjeAction;
use UserFrosting\Sprinkle\Admin\Controller\User\UserUpdateFieldAction;
use UserFrosting\Sprinkle\Admin\Middlewares\UserInjector;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;

/*
 * Routes for administrative user management.
 */
class UsersRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->group('/api/users', function (RouteCollectorProxy $group) {
            $group->get('/u/{user_name}', UserApi::class)
                  ->add(UserInjector::class)
                  ->setName('api_user');
            $group->get('', UsersSprunjeAction::class)
                  ->setName('api_users');
            $group->delete('/u/{user_name}', UserDeleteAction::class)
                  ->add(UserInjector::class)
                  ->setName('api.users.delete');
            $group->get('/u/{user_name}/activities', UserActivitySprunje::class)
                  ->add(UserInjector::class);
            $group->get('/u/{user_name}/roles', UserRoleSprunje::class)
                  ->add(UserInjector::class);
            $group->get('/u/{user_name}/permissions', UserPermissionSprunje::class)
                  ->add(UserInjector::class);
            $group->post('', UserCreateAction::class)
                  ->setName('api.users.create');
            $group->post('/u/{user_name}/password-reset', UserPasswordResetAction::class)
                  ->add(UserInjector::class)
                  ->setName('api.users.password-reset');
            $group->put('/u/{user_name}', UserEditAction::class)
                  ->add(UserInjector::class)
                  ->setName('api.users.edit');
            $group->put('/u/{user_name}/{field}', UserUpdateFieldAction::class)
                  ->add(UserInjector::class)
                  ->setName('api.users.update-field');
        })->add(AuthGuard::class)->add(NoCache::class);
    }
}
