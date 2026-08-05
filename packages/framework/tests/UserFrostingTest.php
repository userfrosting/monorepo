<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests;

use Psr\Http\Message\ServerRequestInterface;
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
        $userfrosting = $this->getUserFrosting();
        $this->assertSame($this->getApp(), $userfrosting->getApp());
        $this->assertSame($this->getContainer(), $userfrosting->getContainer());
        $this->assertSame(TestSprinkle::class, $userfrosting->getMainSprinkle());
        $this->assertSame(
            $userfrosting->getContainer()->get(SprinkleManager::class),
            $this->getService(SprinkleManager::class)
        );
    }

    /**
     * Test ServerRequestInterface is properly created.
     */
    public function testService(): void
    {
        $this->assertSame(
            $this->getContainer()->get(ServerRequestInterface::class),
            $this->getService(ServerRequestInterface::class)
        );
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
