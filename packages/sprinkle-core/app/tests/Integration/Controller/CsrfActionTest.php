<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Controller;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Sprinkle\Core\Csrf\CsrfGuard;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Tests CsrfAction class.
 */
class CsrfActionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCsrf(): void
    {
        // Mock CsrfGuard to control the token values returned
        $csrfGuardMock = Mockery::mock(CsrfGuard::class)
            ->shouldReceive('getTokenNameKey')->andReturn('X-CSRF-Token-Name')
            ->shouldReceive('getTokenValueKey')->andReturn('X-CSRF-Token-Value')
            ->shouldReceive('getTokenName')->andReturn('mocked_token_name')
            ->shouldReceive('getTokenValue')->andReturn('mocked_token_value')
            ->getMock();
        $this->ci->set(CsrfGuard::class, $csrfGuardMock);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/csrf');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertSame('mocked_token_name', $response->getHeaderLine('X-CSRF-Token-Name'));
        $this->assertSame('mocked_token_value', $response->getHeaderLine('X-CSRF-Token-Value'));
        $this->assertResponse('', $response);
    }
}
