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
use UserFrosting\Alert\AlertStream;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerMiddleware;
use UserFrosting\Sprinkle\Core\Error\Handler\AlertStreamExceptionHandler;
use UserFrosting\Sprinkle\Core\Exceptions\UserFacingException;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;
use UserFrosting\Support\Message\UserMessage;

/**
 * Test the handler for AlertStreamExceptionHandler.
 */
class AlertStreamExceptionHandlerTest extends TestCase
{
    protected string $mainSprinkle = AlertStreamExceptionTestSprinkle::class;

    protected function setUp(): void
    {
        parent::setUp();

        // Register the AlertStreamExceptionHandler
        $handler = $this->ci->get(ExceptionHandlerMiddleware::class);
        $handler->registerHandler(UserFacingException::class, AlertStreamExceptionHandler::class, true);
    }

    public function testHTML(): void
    {
        /** @var AlertStream */
        $ms = $this->ci->get(AlertStream::class);
        $ms->resetMessageStream();

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/UserFacingException');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);

        // Test message
        $messages = $ms->getAndClearMessages();
        $this->assertCount(1, $messages);
        $this->assertSame('danger', end($messages)['type']);
        $this->assertSame('Foo: Bar is bar', end($messages)['message']);
    }

    public function testJson(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/UserFacingException');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Foo',
            'description' => 'Bar is bar',
            'status'      => 400
        ], $response);

        // Test message
        /** @var AlertStream */
        $ms = $this->ci->get(AlertStream::class);
        $messages = $ms->getAndClearMessages();
        $this->assertCount(1, $messages);
        $this->assertSame('danger', end($messages)['type']);
        $this->assertSame('Foo: Bar is bar', end($messages)['message']);
    }
}

class AlertStreamExceptionTestRoutes implements RouteDefinitionInterface
{
    public function register(SlimApp $app): void
    {
        $app->get('/UserFacingException', function () {
            $e = new UserFacingException();
            $e->setTitle('Foo');
            $e->setDescription(new UserMessage('Bar is {{foo}}', ['foo' => 'bar']));
            throw $e;
        });
    }
}

class AlertStreamExceptionTestSprinkle extends Core
{
    public function getRoutes(): array
    {
        return [
            AlertStreamExceptionTestRoutes::class,
        ];
    }
}
