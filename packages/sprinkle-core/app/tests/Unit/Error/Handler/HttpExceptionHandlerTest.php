<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Error\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Exception\HttpException;
use Slim\Psr7\Factory\ResponseFactory;
use UserFrosting\Config\Config;
use UserFrosting\I18n\DictionaryInterface;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\Error\Handler\ExceptionHandler;
use UserFrosting\Sprinkle\Core\Error\Handler\HttpExceptionHandler;
use UserFrosting\Sprinkle\Core\Error\Renderer\ErrorRendererInterface;
use UserFrosting\Sprinkle\Core\Error\Renderer\PrettyPageRenderer;
use UserFrosting\Sprinkle\Core\Log\ErrorLoggerInterface;
use UserFrosting\Sprinkle\Core\Tests\Unit\Error\TestException;

/**
 * ExceptionHandler Test
 */
class HttpExceptionHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandle(): void
    {
        // Mock Config to control the settings
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldNotReceive('get')
            ->getMock();

        /** @var Mockery\MockInterface&ErrorRendererInterface $renderer */
        $renderer = Mockery::mock(ErrorRendererInterface::class)
            ->shouldReceive('render')->once()->andReturn('Some body')
            ->getMock();

        // Mock CI and decide witch renderer is passed
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with(PrettyPageRenderer::class)->once()->andReturn($renderer) // Display
            ->getMock();

        // Return from getBody of response
        /** @var Mockery\MockInterface&StreamInterface $streamInterface */
        $streamInterface = Mockery::mock(StreamInterface::class)
            ->shouldReceive('write')->once()
            ->getMock();

        // Mocked response
        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getBody')->once()->andReturn($streamInterface)
            ->shouldReceive('withStatus')->with(500)->once()->andReturnSelf()
            ->shouldReceive('withHeader')->with('Content-type', 'text/html')->once()->andReturnSelf()
            ->getMock();

        // Mock for ResponseFactory
        /** @var Mockery\MockInterface&ResponseFactory $responseFactory */
        $responseFactory = Mockery::mock(ResponseFactory::class)
            ->shouldReceive('createResponse')->with(500)->once()->andReturn($response)
            ->getMock();

        // Dictionary for the translator mock
        /** @var Mockery\MockInterface&DictionaryInterface $dictionary */
        $dictionary = Mockery::mock(DictionaryInterface::class)
            ->shouldReceive('has')->with('ERROR.500.TITLE')->times(1)->andReturn(true)
            ->shouldReceive('has')->with('ERROR.500.DESCRIPTION')->times(1)->andReturn(true)
            ->getMock();

        /** @var Translator $translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('getDictionary')->times(2)->andReturn($dictionary)
            ->shouldReceive('translate')->with('ERROR.500.TITLE')->times(1)->andReturn('Error msg')
            ->shouldReceive('translate')->with('ERROR.500.DESCRIPTION')->times(1)->andReturn('Error description')
            ->getMock();

        /** @var Mockery\MockInterface&ErrorLoggerInterface $logger */
        $logger = Mockery::mock(ErrorLoggerInterface::class);

        /** @var Mockery\MockInterface&ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getMethod')->times(1)->andReturn('GET')
            ->shouldReceive('getHeaderLine')->with('Accept')->once()->andReturn('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8')
            ->getMock();

        // Get handler
        $handler = new HttpExceptionHandler($ci, $responseFactory, $config, $translator, $logger);

        // Do stuff
        $result = $handler->handle($request, new TestException());

        // Assert
        $this->assertSame($response, $result);
    }

    public function testHandleWithHttpException(): void
    {
        // Mock Config to control the settings
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldNotReceive('get')
            ->getMock();

        /** @var Mockery\MockInterface&ErrorRendererInterface $renderer */
        $renderer = Mockery::mock(ErrorRendererInterface::class)
            ->shouldReceive('render')->once()->andReturn('Some body')
            ->getMock();

        // Mock CI and decide witch renderer is passed
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with(PrettyPageRenderer::class)->once()->andReturn($renderer) // Display
            ->getMock();

        // Return from getBody of response
        /** @var Mockery\MockInterface&StreamInterface $streamInterface */
        $streamInterface = Mockery::mock(StreamInterface::class)
            ->shouldReceive('write')->once()
            ->getMock();

        // Mocked response
        /** @var Mockery\MockInterface&ResponseInterface $response */
        $response = Mockery::mock(ResponseInterface::class)
            ->shouldReceive('getBody')->once()->andReturn($streamInterface)
            ->shouldReceive('withStatus')->with(404)->once()->andReturnSelf()
            ->shouldReceive('withHeader')->with('Content-type', 'text/html')->once()->andReturnSelf()
            ->getMock();

        // Mock for ResponseFactory
        /** @var Mockery\MockInterface&ResponseFactory $responseFactory */
        $responseFactory = Mockery::mock(ResponseFactory::class)
            ->shouldReceive('createResponse')->with(404)->once()->andReturn($response)
            ->getMock();

        // Dictionary for the translator mock
        /** @var Mockery\MockInterface&DictionaryInterface $dictionary */
        $dictionary = Mockery::mock(DictionaryInterface::class)
            ->shouldReceive('has')->with('ERROR.404.TITLE')->times(1)->andReturn(true)
            ->shouldReceive('has')->with('ERROR.404.DESCRIPTION')->times(1)->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('getDictionary')->times(2)->andReturn($dictionary)
            ->shouldReceive('translate')->with('ERROR.404.TITLE')->times(1)->andReturn('Error msg')
            ->shouldReceive('translate')->with('ERROR.404.DESCRIPTION')->times(1)->andReturn('Error description')
            ->getMock();

        /** @var Mockery\MockInterface&ErrorLoggerInterface $logger */
        $logger = Mockery::mock(ErrorLoggerInterface::class);

        /** @var Mockery\MockInterface&ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldNotReceive('getMethod')
            ->shouldReceive('getHeaderLine')->with('Accept')->once()->andReturn('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8')
            ->getMock();

        // Exception
        $exception = new HttpException($request, code: 404);

        // Get handler
        $handler = new HttpExceptionHandler($ci, $responseFactory, $config, $translator, $logger);

        // Do stuff
        $result = $handler->handle($request, $exception);

        // Assert
        $this->assertSame($response, $result);
    }
}
