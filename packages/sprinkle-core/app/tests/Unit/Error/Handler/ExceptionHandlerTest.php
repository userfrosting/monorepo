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

use Exception;
use InvalidArgumentException;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Slim\Psr7\Factory\ResponseFactory;
use UserFrosting\Config\Config;
use UserFrosting\I18n\DictionaryInterface;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\Error\Handler\ExceptionHandler;
use UserFrosting\Sprinkle\Core\Error\Renderer\ErrorRendererInterface;
use UserFrosting\Sprinkle\Core\Error\Renderer\PlainTextRenderer;
use UserFrosting\Sprinkle\Core\Error\Renderer\PrettyPageRenderer;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\UserMessageException;
use UserFrosting\Sprinkle\Core\Log\ErrorLoggerInterface;
use UserFrosting\Sprinkle\Core\Tests\Unit\Error\TestException;
use UserFrosting\Support\Exception\BadInstanceOfException;
use UserFrosting\Support\Message\UserMessage;

/**
 * ExceptionHandler Test
 */
class ExceptionHandlerTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testHandle(): void
    {
        // Mock Config to control the settings
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(true)
            ->shouldReceive('get')->with('logs.exception')->once()->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&ErrorRendererInterface $renderer */
        $renderer = Mockery::mock(ErrorRendererInterface::class)
            ->shouldReceive('render')->once()->andReturn('Some body')
            ->getMock();

        /** @var Mockery\MockInterface&ErrorRendererInterface $renderer */
        $textRenderer = Mockery::mock(ErrorRendererInterface::class)
            ->shouldReceive('render')->once()->andReturn('Some text body')
            ->getMock();

        // Mock CI and decide witch renderer is passed
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with(PrettyPageRenderer::class)->once()->andReturn($renderer) // Display
            ->shouldReceive('get')->with(PlainTextRenderer::class)->once()->andReturn($textRenderer) // Logger
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
            ->shouldReceive('has')->with('ERROR.500.TITLE')->times(2)->andReturn(true)
            ->shouldReceive('has')->with('ERROR.500.DESCRIPTION')->times(2)->andReturn(true)
            ->getMock();

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('getDictionary')->times(4)->andReturn($dictionary)
            ->shouldReceive('translate')->with('ERROR.500.TITLE')->times(2)->andReturn('Error msg')
            ->shouldReceive('translate')->with('ERROR.500.DESCRIPTION')->times(2)->andReturn('Error description')
            ->getMock();

        /** @var Mockery\MockInterface&ErrorLoggerInterface $logger */
        $logger = Mockery::mock(ErrorLoggerInterface::class)
            ->shouldReceive('error')->with('Some text body')->once()
            ->getMock();

        /** @var Mockery\MockInterface&ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getMethod')->times(2)->andReturn('GET')
            ->shouldReceive('getHeaderLine')->with('Accept')->once()->andReturn('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8')
            ->getMock();

        // Get handler
        $handler = new ExceptionHandler($ci, $responseFactory, $config, $translator, $logger);

        // Do stuff
        $result = $handler->handle($request, new TestException());

        // Assert
        $this->assertSame($response, $result);
    }

    public function testHandleWithUserMessageStringException(): void
    {
        // Mock Config to control the settings
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(true)
            ->shouldReceive('get')->with('logs.exception')->once()->andReturn(false)
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

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('translate')->with('User %param', ['param' => 'Title'])->times(1)->andReturn('User Title')
            ->shouldReceive('translate')->with('User %param', ['param' => 'Description'])->times(1)->andReturn('User Description')
            ->getMock();

        /** @var Mockery\MockInterface&ErrorLoggerInterface $logger */
        $logger = Mockery::mock(ErrorLoggerInterface::class);

        /** @var Mockery\MockInterface&ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getMethod')->times(1)->andReturn('GET')
            ->shouldReceive('getHeaderLine')->with('Accept')->once()->andReturn('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8')
            ->getMock();

        // Get handler
        $handler = new ExceptionHandler($ci, $responseFactory, $config, $translator, $logger);

        // Do stuff
        $result = $handler->handle($request, new UserMessageTestException());

        // Assert
        $this->assertSame($response, $result);
    }

    public function testHandleWithUserMessageException(): void
    {
        // Mock Config to control the settings
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(true)
            ->shouldReceive('get')->with('logs.exception')->once()->andReturn(false)
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

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('translate')->with('User Title')->times(1)->andReturn('User Title')
            ->shouldReceive('translate')->with('User Description')->times(1)->andReturn('Error description')
            ->getMock();

        /** @var Mockery\MockInterface&ErrorLoggerInterface $logger */
        $logger = Mockery::mock(ErrorLoggerInterface::class);

        /** @var Mockery\MockInterface&ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getMethod')->times(1)->andReturn('GET')
            ->shouldReceive('getHeaderLine')->with('Accept')->once()->andReturn('text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8')
            ->getMock();

        // Get handler
        $handler = new ExceptionHandler($ci, $responseFactory, $config, $translator, $logger);

        // Do stuff
        $result = $handler->handle($request, new MessageTestException());

        // Assert
        $this->assertSame($response, $result);
    }

    public function testRenderResponseWithOptionsAndNoTranslationKeyAndCustomRenderer(): void
    {
        // Mock Config to control the settings
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('debug.exception')->once()->andReturn(true)
            ->getMock();

        // Mock renderer, whichever
        /** @var Mockery\MockInterface&ErrorRendererInterface $renderer */
        $renderer = Mockery::mock(ErrorRendererInterface::class)
            ->shouldReceive('render')->once()->andReturn('Some body')
            ->getMock();

        // Mock CI and decide witch renderer is passed
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with($renderer::class)->once()->andReturn($renderer) // Get custom renderer
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
            ->shouldReceive('withStatus')->with(200)->once()->andReturnSelf()
            ->shouldReceive('withHeader')->with('Content-type', 'foo/bar')->once()->andReturnSelf() // Custom type
            ->getMock();

        // Mock for ResponseFactory
        /** @var Mockery\MockInterface&ResponseFactory $responseFactory */
        $responseFactory = Mockery::mock(ResponseFactory::class)
            ->shouldReceive('createResponse')->with(200)->once()->andReturn($response)
            ->getMock();

        // Dictionary for the translator mock
        /** @var Mockery\MockInterface&DictionaryInterface $dictionary */
        $dictionary = Mockery::mock(DictionaryInterface::class)
            ->shouldReceive('has')->times(2)->andReturn(false) // False
            ->getMock();

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('getDictionary')->times(2)->andReturn($dictionary)
            ->shouldReceive('translate')->with('ERROR.TITLE')->once()->andReturn('Error msg') // Different key
            ->shouldReceive('translate')->with('ERROR.DESCRIPTION')->once()->andReturn('Error description') // Different key
            ->getMock();

        /** @var Mockery\MockInterface&ErrorLoggerInterface $logger */
        $logger = Mockery::mock(ErrorLoggerInterface::class);

        /** @var Mockery\MockInterface&ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getMethod')->once()->andReturn('OPTIONS') // OPTION
            ->shouldReceive('getHeaderLine')->with('Accept')->once()->andReturn('foo/bar') // Custom type
            ->getMock();

        // Get handler
        $handler = new ExceptionHandler($ci, $responseFactory, $config, $translator, $logger);

        // Do stuff
        $handler->registerErrorRenderer('foo/bar', $renderer::class);
        $result = $handler->renderResponse($request, new TestException());

        // Assert
        $this->assertSame($response, $result);
    }

    public function testRenderResponseWithBadRendererFromCi(): void
    {
        // Mock Config to control the settings
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class);

        // Mock CI and decide witch renderer is passed
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with(PrettyPageRenderer::class)->once()->andReturn(new \stdClass()) // Get custom renderer
            ->getMock();

        // Mock for ResponseFactory
        /** @var Mockery\MockInterface&ResponseFactory $responseFactory */
        $responseFactory = Mockery::mock(ResponseFactory::class)
            ->shouldNotReceive('createResponse')
            ->getMock();

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class);

        /** @var Mockery\MockInterface&ErrorLoggerInterface $logger */
        $logger = Mockery::mock(ErrorLoggerInterface::class);

        /** @var Mockery\MockInterface&ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldReceive('getMethod')->once()->andReturn('OPTIONS') // OPTION
            ->shouldReceive('getHeaderLine')->with('Accept')->once()->andReturn('foo/bar') // Custom type
            ->getMock();

        // Get handler
        $handler = new ExceptionHandler($ci, $responseFactory, $config, $translator, $logger);

        // Set expectations
        $this->expectException(BadInstanceOfException::class);

        // Do stuff
        $handler->renderResponse($request, new TestException());
    }

    public function testWriteToErrorLogWithBadRendererFromCi(): void
    {
        // Mock Config to control the settings
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class);

        // Mock CI and decide witch renderer is passed
        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class)
            ->shouldReceive('get')->with(PlainTextRenderer::class)->once()->andReturn(new \stdClass()) // Get custom renderer
            ->getMock();

        /** @var Mockery\MockInterface&ResponseFactory $responseFactory */
        $responseFactory = Mockery::mock(ResponseFactory::class);

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class);

        /** @var Mockery\MockInterface&ErrorLoggerInterface $logger */
        $logger = Mockery::mock(ErrorLoggerInterface::class);

        /** @var Mockery\MockInterface&ServerRequestInterface $request */
        $request = Mockery::mock(ServerRequestInterface::class)
            ->shouldNotReceive('getMethod')
            ->shouldNotReceive('getHeaderLine')
            ->getMock();

        // Get handler
        $handler = new ExceptionHandler($ci, $responseFactory, $config, $translator, $logger);

        // Set expectations
        $this->expectException(BadInstanceOfException::class);

        // Do stuff
        $handler->writeToErrorLog($request, new TestException());
    }

    public function testRegisterErrorRendererWithBadClass(): void
    {
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class);

        /** @var Mockery\MockInterface&ContainerInterface $ci */
        $ci = Mockery::mock(ContainerInterface::class);

        /** @var Mockery\MockInterface&ResponseFactory $responseFactory */
        $responseFactory = Mockery::mock(ResponseFactory::class);

        /** @var Mockery\MockInterface&Translator $translator */
        $translator = Mockery::mock(Translator::class);

        /** @var Mockery\MockInterface&ErrorLoggerInterface $logger */
        $logger = Mockery::mock(ErrorLoggerInterface::class);

        // Get handler
        $handler = new ExceptionHandler($ci, $responseFactory, $config, $translator, $logger);

        // Set expectations
        $this->expectException(InvalidArgumentException::class);

        // Do stuff
        $handler->registerErrorRenderer('foo/bar', \stdClass::class);
    }
}

class MessageTestException extends Exception implements UserMessageException
{
    public function getTitle(): string|UserMessage
    {
        return 'User Title';
    }

    public function getDescription(): string|UserMessage
    {
        return 'User Description';
    }
}

class UserMessageTestException extends Exception implements UserMessageException
{
    public function getTitle(): string|UserMessage
    {
        return new UserMessage('User %param', ['param' => 'Title']);
    }

    public function getDescription(): string|UserMessage
    {
        return new UserMessage('User %param', ['param' => 'Description']);
    }
}
