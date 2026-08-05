<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Authenticate;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;

/**
 * Integration tests for the AuthGuard.
 */
class AuthGuardTest extends AccountTestCase
{
    use MockeryPHPUnitIntegration;

    protected string $mainSprinkle = SprinkleStub::class;

    public function testCheckValid(): void
    {
        // Mock Authenticator
        $authenticator = Mockery::mock(Authenticator::class)
            ->shouldReceive('check')->once()->andReturn(true)
            ->getMock();
        $this->getContainer()->set(Authenticator::class, $authenticator);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/test');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertResponseStatus(200, $response);
        $this->assertResponse('Hello', $response);
    }

    public function testCheckInvalid(): void
    {
        // Mock Authenticator
        $authenticator = Mockery::mock(Authenticator::class)
            ->shouldReceive('check')->once()->andReturn(false)
            ->getMock();
        $this->getContainer()->set(Authenticator::class, $authenticator);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/test');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertResponseStatus(401, $response);
        $body = (string) $response->getBody();
        $this->assertNotSame('Hello', $body);
        $this->assertStringContainsString('Login Required', $body);
    }
}

class SprinkleStub extends Account
{
    public function getRoutes(): array
    {
        return [
            TestRoutes::class,
        ];
    }
}

class TestRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/test', function (Request $request, Response $response) {
            $response->getBody()->write('Hello');

            return $response;
        })->add(AuthGuard::class);
    }
}
