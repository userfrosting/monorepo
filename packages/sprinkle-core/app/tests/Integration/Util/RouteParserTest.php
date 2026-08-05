<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Twig;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Psr\Http\Message\UriInterface;
use Slim\App;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Util\RouteParser;
use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;

/**
 * Tests RoutesExtension.
 */
class RouteParserTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    private RouteParser $parser;

    /**
     * Create parser manually to set basepath
     */
    public function setUp(): void
    {
        parent::setUp();

        /** @var App<\DI\Container> */
        $app = $this->getService(App::class);
        $collector = $app->getRouteCollector();
        $collector->setBasePath('/Myfoo');

        $this->parser = new RouteParser($collector);
    }

    public function testRelativeUrlFor(): void
    {
        // Valid
        $this->assertSame('/api/config', $this->parser->relativeUrlFor('api.config'));

        // Invalid, with fallback
        $this->assertSame('/fallback', $this->parser->relativeUrlFor('invalid', fallbackRoute: '/fallback'));

        // Invalid, no fallback
        $this->expectExceptionMessage('Named route does not exist for name: invalid');
        $this->parser->relativeUrlFor('invalid');
    }

    public function testUrlFor(): void
    {
        // Valid
        $this->assertSame('/Myfoo/api/config', $this->parser->urlFor('api.config'));

        // Invalid, with fallback
        $this->assertSame('/Myfoo/fallback', $this->parser->urlFor('invalid', fallbackRoute: '/fallback'));

        // Invalid, no fallback
        $this->expectExceptionMessage('Named route does not exist for name: invalid');
        $this->parser->urlFor('invalid');
    }

    public function testFullUrlFor(): void
    {
        /** @var UriInterface */
        $uri = Mockery::mock(UriInterface::class)
            ->shouldReceive('getScheme')->times(2)->andReturn('http')
            ->shouldReceive('getAuthority')->times(2)->andReturn('localhost')
            ->getMock();

        // Valid
        $this->assertSame('http://localhost/Myfoo/api/config', $this->parser->fullUrlFor($uri, 'api.config'));

        // Invalid, with fallback
        $this->assertSame('http://localhost/Myfoo/fallback', $this->parser->fullUrlFor($uri, 'invalid', fallbackRoute: '/fallback'));

        // Invalid, no fallback
        $this->expectExceptionMessage('Named route does not exist for name: invalid');
        $this->parser->fullUrlFor($uri, 'invalid');
    }

    public function testService(): void
    {
        /** @var RouteParserInterface */
        $parser = $this->getService(RouteParserInterface::class);

        $this->assertSame('/api/config', $parser->relativeUrlFor('api.config'));
        $this->assertSame('/fallback', $parser->relativeUrlFor('invalid', fallbackRoute: '/fallback'));
        $this->expectExceptionMessage('Named route does not exist for name: invalid');
        $parser->relativeUrlFor('invalid');
    }
}
