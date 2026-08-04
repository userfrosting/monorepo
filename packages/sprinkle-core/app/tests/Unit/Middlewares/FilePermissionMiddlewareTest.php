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

use Illuminate\Cache\Repository as Cache;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use phpmock\mockery\PHPMockery;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Exceptions\BadConfigException;
use UserFrosting\Sprinkle\Core\Middlewares\FilePermissionMiddleware;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class FilePermissionMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testWithPathNotFound(): void
    {
        $config = new Config([
            'writable' => [
                'foo://' => true,
            ],
        ]);

        /** @var Mockery\MockInterface&RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldNotReceive('handle')
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class);

        /** @var Mockery\MockInterface&ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->once()->with('foo://')->andReturn(null)
            ->shouldReceive('findResource')->once()->with('foo://', false, true)->andReturn('app/foo')
            ->getMock();

        // Set Expectation
        $this->expectException(BadConfigException::class);
        $this->expectExceptionMessage("Stream foo:// doesn't exist and is not writeable. Make sure path `app/foo` exist and is writeable.");

        /** @var Mockery\MockInterface&Cache */
        $cache = Mockery::mock(Cache::class);

        $middleware = new FilePermissionMiddleware($locator, $config, $cache);
        $middleware->process($request, $handler);
    }

    public function testWithWritable(): void
    {
        $config = new Config([
            'writable' => [
                'foo://' => true,
            ],
        ]);

        // Mock built-in is_writable
        $reflection_class = new ReflectionClass(FilePermissionMiddleware::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'is_writable')->andReturn(true);

        /** @var Mockery\MockInterface&RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(ServerRequestInterface::class))
            ->andReturn(Mockery::mock(ResponseInterface::class))
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class);

        /** @var Mockery\MockInterface&ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->once()->with('foo://')->andReturn('app/foo')
            ->getMock();

        /** @var Mockery\MockInterface&Cache */
        $cache = Mockery::mock(Cache::class);

        $middleware = new FilePermissionMiddleware($locator, $config, $cache);
        $middleware->process($request, $handler);
    }

    public function testWithNotWritable(): void
    {
        $config = new Config([
            'writable' => [
                'foo://' => true,
            ],
        ]);

        // Mock built-in is_writable
        $reflection_class = new ReflectionClass(FilePermissionMiddleware::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'is_writable')->andReturn(false);

        /** @var Mockery\MockInterface&RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldNotReceive('handle')
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class);

        /** @var Mockery\MockInterface&ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->once()->with('foo://')->andReturn('app/foo')
            ->shouldReceive('findResource')->once()->with('foo://', false, true)->andReturn('app/foo')
            ->getMock();

        // Set Expectation
        $this->expectException(BadConfigException::class);
        $this->expectExceptionMessage("Stream foo:// doesn't exist and is not writeable. Make sure path `app/foo` exist and is writeable.");

        /** @var Mockery\MockInterface&Cache */
        $cache = Mockery::mock(Cache::class);

        $middleware = new FilePermissionMiddleware($locator, $config, $cache);
        $middleware->process($request, $handler);
    }

    public function testWithNotConfig(): void
    {
        $config = new Config([
            'writable' => [
                'foo://' => null,
            ],
        ]);

        /** @var Mockery\MockInterface&RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldNotReceive('handle')
            ->once()
            ->with(Mockery::type(ServerRequestInterface::class))
            ->andReturn(Mockery::mock(ResponseInterface::class))
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class);

        /** @var Mockery\MockInterface&ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class);

        /** @var Mockery\MockInterface&Cache */
        $cache = Mockery::mock(Cache::class);

        $middleware = new FilePermissionMiddleware($locator, $config, $cache);
        $middleware->process($request, $handler);
    }

    public function testWithCacheHit(): void
    {
        $config = new Config([
            'cache' => [
                'file_permission' => [
                    'key' => 'uf_file_permissions',
                    'ttl' => 3600,
                ],
            ],
            'writable' => ['foo://' => true],
        ]);

        /** @var Mockery\MockInterface&RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(ServerRequestInterface::class))
            ->andReturn(Mockery::mock(ResponseInterface::class))
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class);

        // Locator must never be called on a cache hit
        /** @var Mockery\MockInterface&ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldNotReceive('findResource')
            ->getMock();

        /** @var Mockery\MockInterface&Cache */
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('has')->once()->with('uf_file_permissions')->andReturn(true)
            ->getMock();

        $middleware = new FilePermissionMiddleware($locator, $config, $cache);
        $middleware->process($request, $handler);
    }

    public function testWithCacheMiss(): void
    {
        $config = new Config([
            'cache' => [
                'file_permission' => [
                    'key' => 'uf_file_permissions',
                    'ttl' => 3600,
                ],
            ],
            'writable' => ['foo://' => true],
        ]);

        // Mock built-in is_writable
        $reflection_class = new ReflectionClass(FilePermissionMiddleware::class);
        $namespace = $reflection_class->getNamespaceName();
        PHPMockery::mock($namespace, 'is_writable')->andReturn(true);

        /** @var Mockery\MockInterface&RequestHandlerInterface */
        $handler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldReceive('handle')
            ->once()
            ->with(Mockery::type(ServerRequestInterface::class))
            ->andReturn(Mockery::mock(ResponseInterface::class))
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface */
        $request = Mockery::mock(ServerRequestInterface::class);

        /** @var Mockery\MockInterface&ResourceLocatorInterface */
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->once()->with('foo://')->andReturn('app/foo')
            ->getMock();

        /** @var Mockery\MockInterface&Cache */
        $cache = Mockery::mock(Cache::class)
            ->shouldReceive('has')->once()->with('uf_file_permissions')->andReturn(false)
            ->shouldReceive('put')->once()->with('uf_file_permissions', true, 3600)
            ->getMock();

        $middleware = new FilePermissionMiddleware($locator, $config, $cache);
        $middleware->process($request, $handler);
    }
}
