<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Testing;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Http\Message\ResponseInterface;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests RegisterAction
 */
class WithTestUserTest extends AccountTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;
    use WithTestUser;

    protected string $mainSprinkle = TestSprinkle::class;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    /**
     * Test base functionality.
     */
    public function testNoUser(): void
    {
        $request = $this->createJsonRequest('GET', '/test');
        $response = $this->handleRequest($request);
        $this->assertResponseStatus(401, $response);
    }

    public function testWithUser(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);
        $request = $this->createJsonRequest('GET', '/test');
        $response = $this->handleRequest($request);
        $this->assertJsonResponse([
            'roles'       => [],
            'permissions' => [],
        ], $response);
        $this->assertResponseStatus(200, $response);
    }

    public function testWithUserWithRoles(): void
    {
        /** @var User */
        $user = User::factory()->create();

        /** @var Role */
        $role = Role::factory()->create();

        $this->actAsUser($user, roles: [$role]);
        $request = $this->createJsonRequest('GET', '/test');
        $response = $this->handleRequest($request);
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'roles'       => [$role->slug],
            'permissions' => [],
        ], $response);
    }

    public function testWithUserWithPermissions(): void
    {
        /** @var User */
        $user = User::factory()->create();

        $this->actAsUser($user, permissions: ['test_permissions']);
        $request = $this->createJsonRequest('GET', '/test');
        $response = $this->handleRequest($request);
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse(['test_permissions'], $response, 'permissions');
    }
}

class TestSprinkle extends Account
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
    public function register(\Slim\App $app): void
    {
        $app->get('/test', function (ResponseInterface $response, Authenticator $authenticator) {
            /** @var UserInterface */
            $user = $authenticator->user();

            $data = [
                'roles'       => $user->roles->pluck('slug')->toArray(),
                'permissions' => $user->permissions->pluck('slug')->toArray(),
            ];

            $payload = json_encode($data, JSON_THROW_ON_ERROR);
            $response->getBody()->write($payload);

            return $response->withHeader('Content-Type', 'application/json');
        })->add(AuthGuard::class);
    }
}
