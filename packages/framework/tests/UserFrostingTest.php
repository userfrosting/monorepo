<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Testing\TestCase;
use UserFrosting\Tests\TestSprinkle\TestSprinkle;
use UserFrosting\UserFrosting;

/**
 * Tests UserFrosting class.
 */
class UserFrostingTest extends TestCase
{
    protected string $mainSprinkle = TestSprinkle::class;

    public function testGetters(): void
    {
        $this->assertInstanceOf(App::class, $this->userfrosting->getApp()); // @phpstan-ignore-line
        $this->assertInstanceOf(ContainerInterface::class, $this->userfrosting->getContainer()); // @phpstan-ignore-line
        $this->assertSame(TestSprinkle::class, $this->userfrosting->getMainSprinkle());
        $this->assertInstanceOf(SprinkleManager::class, $this->userfrosting->getContainer()->get(SprinkleManager::class)); // @phpstan-ignore-line
    }

    /**
     * Test ServerRequestInterface is properly created.
     */
    public function testService(): void
    {
        $this->assertInstanceOf(ServerRequestInterface::class, $this->ci->get(ServerRequestInterface::class)); // @phpstan-ignore-line
    }

    /**
     * Test a basic Hello World Page
     */
    public function testFullRoute(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createRequest('GET', '/foo');
        $response = $this->handleRequest($request);

        // Asserts
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse(['Great work! Keep going!', 'Great work! Keep going!', 'bar'], $response);
    }
}
