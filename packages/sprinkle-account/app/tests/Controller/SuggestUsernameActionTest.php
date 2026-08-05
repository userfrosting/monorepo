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
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;

class SuggestUsernameActionTest extends AccountTestCase
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

    public function testNormal(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/suggest-username');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertNotJsonResponse('', $response, 'user_name');
    }

    public function testWithEmptyResult(): void
    {
        // Fake all user suggestions are taken
        $userModel = Mockery::mock(UserInterface::class);
        $userModel->shouldReceive('firstWhere')->andReturn(true);
        $this->getContainer()->set(UserInterface::class, $userModel);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/suggest-username');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse('', $response, 'user_name');
    }

    public function testWithThrottler(): void
    {
        // Create fake throttler
        $throttler = Mockery::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('suggest_username')->andReturn(90);
        $this->getContainer()->set(Throttler::class, $throttler);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/suggest-username');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Rate Limit Exceeded', $response, 'title');
        $this->assertResponseStatus(429, $response);
    }
}
