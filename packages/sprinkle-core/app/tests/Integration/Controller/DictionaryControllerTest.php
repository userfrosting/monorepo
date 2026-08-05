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

use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Http\Message\ResponseInterface as Response;
use UserFrosting\I18n\DictionaryInterface;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;

class DictionaryControllerTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    public function testGetDictionaryDataReturnsExpectedArray(): void
    {
        // Define mocks
        $dictionaryMock = Mockery::mock(DictionaryInterface::class);
        $cacheMock = Mockery::mock(Cache::class);

        $dictionaryMock->shouldReceive('getLocale->getIdentifier')->andReturn('en_US');
        $dictionaryMock->shouldReceive('getLocale->getConfig')->andReturn(['config_key' => 'config_value']);
        $dictionaryMock->shouldReceive('getFlattenDictionary')->andReturn(['key' => 'value']);

        $cacheMock->shouldReceive('rememberForever')->andReturnUsing(function ($key, $callback) {
            return $callback();
        });

        // Set mocks in the CI
        $this->getContainer()->set(DictionaryInterface::class, $dictionaryMock);
        $this->getContainer()->set(Cache::class, $cacheMock);

        // Set expectations
        $expected = [
            'identifier' => 'en_US',
            'config'     => ['config_key' => 'config_value'],
            'dictionary' => ['key' => 'value'],
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/api/dictionary');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse($expected, $response);
    }
}
