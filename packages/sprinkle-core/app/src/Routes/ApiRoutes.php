<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Routes;

use Slim\App;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Core\Controller\ConfigController;
use UserFrosting\Sprinkle\Core\Controller\CsrfAction;
use UserFrosting\Sprinkle\Core\Controller\DictionaryController;

class ApiRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/api/config', ConfigController::class)->setName('api.config');
        $app->get('/api/dictionary', DictionaryController::class)->setName('api.dictionary');
        $app->get('/api/csrf', CsrfAction::class)->setName('api.csrf');
    }
}
