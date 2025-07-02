<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Controller\Config;

use Illuminate\Database\Connection;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Testing\WithTestUser;
use UserFrosting\Sprinkle\Admin\Controller\Config\SystemInfoApiAction;
use UserFrosting\Sprinkle\Admin\Tests\AdminTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class SystemInfoApiActionTest extends AdminTestCase
{
    use RefreshDatabase;
    use WithTestUser;
    use MockeryPHPUnitIntegration;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testPageForGuestUser(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/config/info');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Login Required', $response, 'title');
        $this->assertResponseStatus(401, $response);
    }

    public function testPageForForbiddenException(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/config/info');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Access Denied', $response, 'title');
        $this->assertResponseStatus(403, $response);
    }

    public function testPage(): void
    {
        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, permissions: ['uri_dashboard']);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/api/config/info');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonStructure([
            'frameworkVersion',
            'phpVersion',
            'database',
            'server',
            'projectPath',
            'sprinkles',
        ], $response);
    }

    public function testPageWithPDOException(): void
    {
        // Mock PDO
        $pdo = Mockery::mock(\PDO::class)
            ->shouldReceive('getAttribute')->with(\PDO::ATTR_DRIVER_NAME)->andThrow(new \PDOException())->once()
            ->shouldReceive('getAttribute')->with(\PDO::ATTR_SERVER_VERSION)->andThrow(new \PDOException())->once()
            ->getMock();

        // Mock Connection
        /** @var Connection */
        $connection = Mockery::mock(Connection::class)
            ->shouldReceive('getDatabaseName')->andReturn('database name')
            ->shouldReceive('getPdo')->andReturn($pdo)
            ->getMock();

        // Create fake controller, inject mocked connection and set it in container
        $controller = $this->ci->make(SystemInfoApiAction::class, [
            'dbConnection' => $connection,
        ]);
        $this->ci->set(SystemInfoApiAction::class, $controller);

        /** @var User */
        $user = User::factory()->create();
        $this->actAsUser($user, isMaster: true);

        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/api/config/info');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse('database name', $response, 'database.name');
    }
}
