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
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;

/**
 * Tests RegisterAction
 */
class CheckUsernameActionTest extends AccountTestCase
{
    use MockeryPHPUnitIntegration;
    use RefreshDatabase;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testCheckUsername(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/check-username')
                        ->withQueryParams(['user_name' => 'potato']);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'available' => true,
            'message'   => '',
        ], $response);
        $this->assertResponseStatus(200, $response);
    }

    public function testCheckUsernameWithNoData(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/check-username');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'available' => true,
            'message'   => '',
        ], $response);
        $this->assertResponseStatus(200, $response);
    }

    public function testCheckUsernameWithUsernameNotAvailable(): void
    {
        // Create test user
        /** @var User */
        $user = User::factory()->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/check-username')
                        ->withQueryParams(['user_name' => $user->user_name]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'available' => false,
            'message'   => "Username <strong>{$user->user_name}</strong> is not available. Choose a different name, or click 'suggest'.",
        ], $response);
        $this->assertResponseStatus(200, $response);
    }

    public function testCheckUsernameWithThrottler(): void
    {
        // Create fake throttler
        $throttler = Mockery::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('check_username_request')->andReturn(90);
        $this->getContainer()->set(Throttler::class, $throttler);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/check-username');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Rate Limit Exceeded', $response, 'title');
        $this->assertResponseStatus(429, $response);
    }
}
