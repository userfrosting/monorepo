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

use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Tests ConfigController class.
 */
class ConfigControllerTest extends TestCase
{
    public function testConfig(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/config');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonStructure(['site', 'locales', 'csrf'], $response);
    }
}
