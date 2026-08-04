<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Middlewares;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use Psr\Http\Server\RequestHandlerInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Middlewares\URIMiddleware;

class URIMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testWithConfig(): void
    {
        $config = new Config([
            'site' => [
                'uri' => [
                    'public' => 'http://example.com',
                ],
            ],
        ]);

        /** @var Mockery\MockInterface&RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(ServerRequestInterface::class))
            ->andReturn(Mockery::mock(ResponseInterface::class))
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldNotReceive('getUri')
            ->getMock();

        $middleware = new URIMiddleware($config);
        $middleware->process($request, $handler);

        $this->assertSame('http://example.com', $config->get('site.uri.public'));
    }

    public function testWithNotConfig(): void
    {
        $config = new Config([
            'site' => [
                'uri' => [
                    'public' => null,
                ],
            ],
        ]);

        $uri = Mockery::mock(UriInterface::class)
            ->shouldReceive('getScheme')->once()->andReturn('http')
            ->shouldReceive('getAuthority')->once()->andReturn('example.com')
            ->getMock();

        /** @var Mockery\MockInterface&RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(ServerRequestInterface::class))
            ->andReturn(Mockery::mock(ResponseInterface::class))
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getUri')->andReturn($uri)
            ->getMock();

        $middleware = new URIMiddleware($config);
        $middleware->process($request, $handler);

        $this->assertSame('http://example.com', $config->get('site.uri.public'));
    }

    public function testWithPort(): void
    {
        $config = new Config([
            'site' => [
                'uri' => [
                    'public' => null,
                ],
            ],
        ]);

        $uri = Mockery::mock(UriInterface::class)
            ->shouldReceive('getScheme')->once()->andReturn('https')
            ->shouldReceive('getAuthority')->once()->andReturn('localhost:8888')
            ->getMock();

        /** @var Mockery\MockInterface&RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(ServerRequestInterface::class))
            ->andReturn(Mockery::mock(ResponseInterface::class))
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getUri')->andReturn($uri)
            ->getMock();

        $middleware = new URIMiddleware($config);
        $middleware->process($request, $handler);

        $this->assertSame('https://localhost:8888', $config->get('site.uri.public'));
    }
}
