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

use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;

class ContentControllerTest extends CoreTestCase
{
    public function testSimpleFile(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/c/Privacy');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([], $response, 'metadata');
        $this->assertStringContainsString('Lorem ipsum dolor', (string) $response->getBody());
    }

    public function testWithTrailingSlash(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/c/Privacy/');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([], $response, 'metadata');
        $this->assertStringContainsString('Lorem ipsum dolor', (string) $response->getBody());
    }

    public function testNotFound(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/c/foo');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(404, $response);
        $this->assertJsonResponse('Not Found', $response, 'title');
    }
}
