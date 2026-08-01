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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use phpmock\mockery\PHPMockery;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use ReflectionClass;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Error\ShutdownHandler;
use UserFrosting\Testing\CustomAssertionsTrait;

/**
 * Test ShutdownHandler
 */
class ShutdownHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;
    use CustomAssertionsTrait;

    /** @var (string|int)[] $error */
    public static array $error = [
        'type'    => E_ERROR,
        'message' => 'Undefined variable: a',
        'file'    => 'C:\WWW\index.php',
        'line'    => 2,
    ];

    public function testBuildJsonErrorWithDetails(): void
    {
        /** @var Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(true)
            ->getMock();

        $request = Mockery::mock(ServerRequestInterface::class);

        // Get handler
        $handler = new ShutdownHandler($config, $request);

        // Do stuff
        $result = $handler->buildJsonError($this::$error);

        // Assert
        $this->assertJson($result);
        $this->assertJsonStructure(array_keys($this::$error), $result);
    }

    public function testBuildJsonErrorWithoutDetails(): void
    {
        /** @var Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(false)
            ->getMock();

        $request = Mockery::mock(ServerRequestInterface::class);

        // Get handler
        $handler = new ShutdownHandler($config, $request);

        // Do stuff
        $result = $handler->buildJsonError($this::$error);

        // Assert
        $this->assertJson($result);
        $this->assertJsonStructure(['message'], $result);
    }

    public function testBuildTxtErrorWithDetails(): void
    {
        /** @var Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(true)
            ->getMock();

        $request = Mockery::mock(ServerRequestInterface::class);

        // Get handler
        $handler = new ShutdownHandler($config, $request);

        // Do stuff
        $result = $handler->buildTxtError($this::$error);

        // Assert
        $this->assertSame('Fatal error: Undefined variable: a in C:\WWW\index.php on line 2', $result);
    }

    public function testBuildTxtErrorWithoutDetails(): void
    {
        /** @var Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(false)
            ->getMock();

        $request = Mockery::mock(ServerRequestInterface::class);

        // Get handler
        $handler = new ShutdownHandler($config, $request);

        // Do stuff
        $result = $handler->buildTxtError($this::$error);

        // Assert (we don't care what the actual message is here)
        $this->assertIsString($result); // @phpstan-ignore-line
    }

    public function testBuildHtmlErrorWithDetails(): void
    {
        /** @var Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(true)
            ->getMock();

        $request = Mockery::mock(ServerRequestInterface::class);

        // Get handler
        $handler = new ShutdownHandler($config, $request);

        // Do stuff
        $result = $handler->buildHtmlError($this::$error);

        // Assert
        $this->assertHtmlTagCount(1, $result, 'html');
    }

    public function testBuildHtmlErrorWithoutDetails(): void
    {
        /** @var Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(false)
            ->getMock();

        $request = Mockery::mock(ServerRequestInterface::class);

        // Get handler
        $handler = new ShutdownHandler($config, $request);

        // Do stuff
        $result = $handler->buildHtmlError($this::$error);

        // Assert
        $this->assertHtmlTagCount(1, $result, 'html');
    }

    public function testHandleWithNull(): void
    {
        // Get namespace of ShutdownHandler class for mock
        $reflection_class = new ReflectionClass(ShutdownHandler::class);
        $namespace = $reflection_class->getNamespaceName();

        // Mock built-in error_get_last
        PHPMockery::mock($namespace, 'error_get_last')->andReturn(null);

        $config = Mockery::mock(Config::class);
        $request = Mockery::mock(ServerRequestInterface::class);

        // Get handler, mock  the terminate method.
        /** @var ShutdownHandler $handler */
        $handler = Mockery::mock(ShutdownHandler::class, [$config, $request])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldNotReceive('terminate')
            ->getMock();

        // Do stuff
        $handler->handle();
    }

    public function testHandle(): void
    {
        // Get namespace of ShutdownHandler class for mock
        $reflection_class = new ReflectionClass(ShutdownHandler::class);
        $namespace = $reflection_class->getNamespaceName();

        // Mock built-in error_get_last
        PHPMockery::mock($namespace, 'error_get_last')->andReturn($this::$error);

        // Mock built-in php_sapi_name
        PHPMockery::mock($namespace, 'php_sapi_name')->andReturn('cli');

        /** @var Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(false)
            ->getMock();

        $request = Mockery::mock(ServerRequestInterface::class);

        // Get handler, mock  the terminate method.
        /** @var ShutdownHandler $handler */
        $handler = Mockery::mock(ShutdownHandler::class, [$config, $request])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('terminate')->once()
            ->getMock();

        // Do stuff
        $handler->handle();
    }

    /**
     * @testWith ["text/html"]
     *           ["application/json"]
     *           ["text/xml"]
     */
    public function testHandleWithNonCli(string $contentType): void
    {
        // Get namespace of ShutdownHandler class for mock
        $reflection_class = new ReflectionClass(ShutdownHandler::class);
        $namespace = $reflection_class->getNamespaceName();

        // Mock built-in error_get_last
        PHPMockery::mock($namespace, 'error_get_last')->andReturn($this::$error);

        // Mock built-in php_sapi_name
        PHPMockery::mock($namespace, 'php_sapi_name')->andReturn('apache');

        /** @var Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(false)
            ->getMock();

        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getHeaderLine')->with('Accept')->once()->andReturn($contentType)
            ->getMock();

        // Get handler, mock  the terminate method.
        /** @var ShutdownHandler $handler */
        $handler = Mockery::mock(ShutdownHandler::class, [$config, $request])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('terminate')->once()
            ->getMock();

        // Do stuff
        $handler->handle();
    }

    #[RunInSeparateProcess]
    public function testRegister(): void
    {
        // Get namespace of ShutdownHandler class for mock
        $reflection_class = new ReflectionClass(ShutdownHandler::class);
        $namespace = $reflection_class->getNamespaceName();

        // Mock built-in register_shutdown_function and make sure it's called
        PHPMockery::mock($namespace, 'register_shutdown_function')->once();

        $config = Mockery::mock(Config::class);
        $request = Mockery::mock(ServerRequestInterface::class);

        // Get handler
        $handler = new ShutdownHandler($config, $request);

        // Do stuff
        $handler->register();
    }
}
