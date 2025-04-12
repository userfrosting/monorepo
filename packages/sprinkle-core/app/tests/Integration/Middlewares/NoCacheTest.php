<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Slim\App;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Tests NoCache Middleware.
 */
class NoCacheTest extends TestCase
{
    protected string $mainSprinkle = NoCacheSprinkle::class;

    public function testControl(): void
    {
        $request = $this->createJsonRequest('GET', '/cache');
        $response = $this->handleRequest($request);
        $this->assertJsonResponse([], $response);
        $this->assertResponseStatus(200, $response);
        $this->assertNotSame('no-store', $response->getHeaderLine('Cache-Control'));
    }

    public function testCacheHeader(): void
    {
        $request = $this->createJsonRequest('GET', '/no-cache');
        $response = $this->handleRequest($request);
        $this->assertJsonResponse([], $response);
        $this->assertResponseStatus(200, $response);
        $this->assertSame('no-store', $response->getHeaderLine('Cache-Control'));
    }
}

class NoCacheTestRoute implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/no-cache', [$this, 'action'])->add(NoCache::class);
        // Control route :
        $app->get('/cache', [$this, 'action']);
    }

    public function action(Response $response): Response
    {
        $payload = json_encode([], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}

class NoCacheSprinkle extends Core
{
    public function getRoutes(): array
    {
        return [
            NoCacheTestRoute::class,
        ];
    }
}
