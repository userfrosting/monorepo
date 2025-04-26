<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\ServicesProvider;

use DI\Container;
use Illuminate\Database\Connection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Session\NullSessionHandler;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use SessionHandlerInterface;
use UserFrosting\Config\Config;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Exceptions\BadConfigException;
use UserFrosting\Sprinkle\Core\ServicesProvider\SessionService;
use UserFrosting\Testing\ContainerStub;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Integration tests for `session` service.
 * Check to see if service returns what it's supposed to return
 */
class SessionServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new SessionService();
        $this->ci = ContainerStub::create($provider->register());
    }

    public function testSession(): void
    {
        // Set mock Config
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('session')->once()->andReturn([])
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Set mock SessionHandlerInterface
        $handler = Mockery::mock(SessionHandlerInterface::class);
        $this->ci->set(SessionHandlerInterface::class, $handler);

        // Assert CI get
        $this->ci->get(Session::class);
    }

    /**
     * Test the right handler is returned depending on Config
     *
     * @dataProvider handlerDataProvider
     *
     * @param string       $name
     * @param class-string $class
     */
    public function testConfig(string $name, string $class): void
    {
        // Set mock Config
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('session.handler')->once()->andReturn($name)
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Set mock Store
        $this->ci->set($class, Mockery::mock($class));

        // Assert CI get
        $this->assertInstanceOf($class, $this->ci->get(SessionHandlerInterface::class)); // @phpstan-ignore-line
    }

    /**
     * @return array<string|class-string>[]
     */
    public static function handlerDataProvider(): array
    {
        return [
            ['file', FileSessionHandler::class],
            ['database', DatabaseSessionHandler::class],
            ['array', NullSessionHandler::class],
        ];
    }

    public function testBadConfig(): void
    {
        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('session.handler')->times(2)->andReturn('foo')
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Get stream and assert the exception is thrown.
        $this->expectException(BadConfigException::class);
        $this->ci->get(SessionHandlerInterface::class);
    }

    public function testFileSessionHandler(): void
    {
        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('session.minutes')->once()->andReturn(120)
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Set mock dependencies
        $this->ci->set(Filesystem::class, Mockery::mock(Filesystem::class));
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->with('sessions://')->once()->andReturn('')
            ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Assert CI get
        $this->ci->get(FileSessionHandler::class);
    }

    public function testFileSessionHandlerWithError(): void
    {
        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldNotReceive('get')->with('session.minutes')
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Set mock dependencies
        $this->ci->set(Filesystem::class, Mockery::mock(Filesystem::class));
        $locator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('findResource')->with('sessions://')->once()->andReturn(null)
            ->getMock();
        $this->ci->set(ResourceLocatorInterface::class, $locator);

        // Set expectations
        $this->expectException(\Exception::class);
        $this->ci->get(FileSessionHandler::class);
    }

    public function testDatabaseSessionHandler(): void
    {
        // Set mock Config service
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('session.database.table')->once()->andReturn('sessions')
            ->shouldReceive('get')->with('session.minutes')->once()->andReturn(120)
            ->getMock();
        $this->ci->set(Config::class, $config);

        // Set mock dependencies
        $this->ci->set(Connection::class, Mockery::mock(Connection::class));

        // Assert CI get
        $this->ci->get(DatabaseSessionHandler::class);
    }
}
