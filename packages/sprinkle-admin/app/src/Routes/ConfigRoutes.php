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
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Admin\Controller\Config\CacheApiAction;
use UserFrosting\Sprinkle\Admin\Controller\Config\SystemInfoApiAction;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;

/*
 * Routes for config apis.
 */
class ConfigRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/api/config/info', SystemInfoApiAction::class)
            ->setName('config.info')
            ->add(AuthGuard::class)
            ->add(NoCache::class);

        $app->post('/api/config/clear-cache', CacheApiAction::class)
            ->setName('config.cache')
            ->add(AuthGuard::class)
            ->add(NoCache::class);
    }
}
