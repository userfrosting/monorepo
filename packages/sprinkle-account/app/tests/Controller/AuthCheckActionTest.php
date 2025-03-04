<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Controller;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests RegisterAction
 */
class AuthCheckActionTest extends AccountTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testGuest(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/auth-check');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'user'        => null,
            'permissions' => null,
        ], $response);
        $this->assertResponseStatus(200, $response);
    }

    public function testAuth(): void
    {
        /** @var User */
        $user = User::factory([
            'password' => 'test'
        ])->create();

        /** @var Role */
        $role = Role::factory()->create();
        $user->roles()->attach($role);
        $permission = new Permission([
            'slug'       => 'test_permission',
            'name'       => 'Test Permission',
            'conditions' => 'always()',
        ]);
        $permission->save();
        $role->permissions()->attach($permission);
        $role->save();

        // Mock Authenticator
        $authenticator = Mockery::mock(Authenticator::class)
            ->shouldReceive('user')->once()->andReturn($user)
            ->getMock();
        $this->ci->set(Authenticator::class, $authenticator);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/auth-check');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'user'        => $user->attributesToArray(),
            'permissions' => ['test_permission' => 'always()'],
        ], $response);
        $this->assertResponseStatus(200, $response);
    }
}
