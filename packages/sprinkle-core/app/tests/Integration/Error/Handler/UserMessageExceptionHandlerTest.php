<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Error\Handler;

use Slim\App as SlimApp;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerMiddleware;
use UserFrosting\Sprinkle\Core\Error\Handler\UserMessageExceptionHandler;
use UserFrosting\Sprinkle\Core\Exceptions\UserFacingException;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test the handler for UserMessageExceptionHandler.
 */
class UserMessageExceptionHandlerTest extends TestCase
{
    protected string $mainSprinkle = UserFacingExceptionTestSprinkle::class;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the UserMessageExceptionHandler
        $handler = $this->ci->get(ExceptionHandlerMiddleware::class);
        $handler->registerHandler(UserFacingException::class, UserMessageExceptionHandler::class, true);
    }

    public function testHTML(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/UserFacingException');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertStringContainsString('Foo Bar Exception', (string) $response->getBody());
        $this->assertStringContainsString('Bar is not a valid value', (string) $response->getBody());
    }

    public function testJson(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/UserFacingException');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Foo Bar Exception',
            'description' => 'Bar is not a valid value',
            'status'      => 400
        ], $response);
    }
}

class UserFacingExceptionTestRoutes implements RouteDefinitionInterface
{
    public function register(SlimApp $app): void
    {
        $app->get('/UserFacingException', function () {
            $e = new UserFacingException();
            $e->setTitle('Foo Bar Exception');
            $e->setDescription('Bar is not a valid value');
            throw $e;
        });
    }
}

class UserFacingExceptionTestSprinkle extends Core
{
    public function getRoutes(): array
    {
        return [
            UserFacingExceptionTestRoutes::class,
        ];
    }
}
