<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Exception;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Exception\HttpException;
use stdClass;
use UserFrosting\Sprinkle\Core\Error\ExceptionHandlerMiddleware;
use UserFrosting\Sprinkle\Core\Error\Handler\ExceptionHandlerInterface;
use UserFrosting\Sprinkle\Core\Tests\Unit\Error\TestException;

/**
 * Test service implementation works
 */
class ExceptionHandlerMiddlewareTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSetDefaultErrorHandlerAndGetDefaultErrorHandler(): void
    {
        // Mock handler
        $handler = Mockery::mock(ExceptionHandlerInterface::class);

        // Mock dependencies
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($handler::class)->once()->andReturn($handler)
            ->getMock();

        // Get Middleware
        $middleware = new ExceptionHandlerMiddleware($ci);

        // Do Stuff
        $default = $middleware->setDefaultErrorHandler($handler::class)->getDefaultErrorHandler();

        // Assert
        $this->assertSame($handler, $default);
    }

    public function testSetDefaultErrorHandlerWithException(): void
    {
        // Mock handler
        $handler = Mockery::mock(stdClass::class);

        // Mock dependencies
        $ci = Mockery::mock(ContainerInterface::class);

        // Get Middleware
        $middleware = new ExceptionHandlerMiddleware($ci);

        // Set expectations
        $this->expectException(InvalidArgumentException::class);

        // Do Stuff
        $middleware->setDefaultErrorHandler($handler::class);
    }

    public function testRegisterHandlerWithException(): void
    {
        // Mock handler
        $handler = Mockery::mock(stdClass::class);

        // Mock dependencies
        $ci = Mockery::mock(ContainerInterface::class);

        // Get Middleware
        $middleware = new ExceptionHandlerMiddleware($ci);

        // Set expectations
        $this->expectException(InvalidArgumentException::class);

        // Do Stuff
        $middleware->registerHandler(TestException::class, $handler::class, true);
    }

    public function testRegisterHandler(): void
    {
        // Mock handler
        $handler = Mockery::mock(ExceptionHandlerInterface::class);

        // Mock dependencies
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($handler::class)->once()->andReturn($handler)
            ->getMock();

        // Get Middleware
        $middleware = new ExceptionHandlerMiddleware($ci);

        // Do Stuff
        $middleware->registerHandler(TestException::class, $handler::class, false);
        $result = $middleware->getErrorHandler(TestException::class);

        // Assert
        $this->assertSame($handler, $result);
    }

    public function testRegisterHandlerAsSubtype(): void
    {
        // Mock handler
        $handler = Mockery::mock(ExceptionHandlerInterface::class);

        // Mock dependencies
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($handler::class)->once()->andReturn($handler)
            ->getMock();

        // Get Middleware
        $middleware = new ExceptionHandlerMiddleware($ci);

        // Do Stuff
        $middleware->registerHandler(TestException::class, $handler::class, true);
        $result = $middleware->getErrorHandler(TestException::class);

        // Assert
        $this->assertSame($handler, $result);
    }

    public function testRegisterHandlerForSubtype(): void
    {
        // Mock handler
        $handler = Mockery::mock(ExceptionHandlerInterface::class);

        // Mock dependencies
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($handler::class)->once()->andReturn($handler)
            ->getMock();

        // Get Middleware
        $middleware = new ExceptionHandlerMiddleware($ci);

        // Do Stuff
        $middleware->registerHandler(Exception::class, $handler::class, true);
        $result = $middleware->getErrorHandler(TestException::class);

        // Assert
        $this->assertSame($handler, $result);
    }

    public function testGetErrorHandlerForDefault(): void
    {
        // Mock handler
        $handler = Mockery::mock(ExceptionHandlerInterface::class);

        // Mock dependencies
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($handler::class)->times(2)->andReturn($handler)
            ->getMock();

        // Get Middleware
        $middleware = new ExceptionHandlerMiddleware($ci);

        // Do Stuff
        // N.B.: Default handler doesn't need to be registered
        $middleware->setDefaultErrorHandler($handler::class);
        $defaultHandler = $middleware->getDefaultErrorHandler();
        $result = $middleware->getErrorHandler(TestException::class);

        // Assert
        $this->assertSame($handler, $result);
        $this->assertSame($handler, $defaultHandler);
    }

    public function testProcess(): void
    {
        // Mock request. Make sure the one returned is the one from the
        // HTTPException, not the one passed
        $request1 = Mockery::mock(ServerRequestInterface::class);
        $request2 = Mockery::mock(ServerRequestInterface::class);

        // Test exception to handle
        /** @var Mockery\MockInterface&HttpException $exception */
        $exception = Mockery::mock(HttpException::class)
            ->shouldReceive('getRequest')->once()->andReturn($request2)
            ->getMock();

        /** @var Mockery\MockInterface&RequestHandlerInterface $requestHandler */
        $requestHandler = Mockery::mock(RequestHandlerInterface::class)
            ->shouldReceive('handle')->with($request1)->once()->andThrow($exception)
            ->getMock();

        // The response returned by the handler
        $response = Mockery::mock(ResponseInterface::class);

        // Mock handler
        $handler = Mockery::mock(ExceptionHandlerInterface::class)
            ->shouldReceive('handle')->with($request2, $exception)->once()->andReturn($response)
            ->getMock();

        // Mock dependencies
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($handler::class)->once()->andReturn($handler)
            ->getMock();

        // Get Middleware
        $middleware = new ExceptionHandlerMiddleware($ci);

        // Do Stuff
        $middleware->registerHandler($exception::class, $handler::class, false);
        $result = $middleware->process($request1, $requestHandler);

        // Assert
        $this->assertSame($response, $result);
    }
}
