<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Error\Renderer;

use Exception;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Request;
use Slim\Views\Twig;
use Twig\Error\LoaderError;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Error\Renderer\PrettyPageRenderer;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\TwigRenderedException;
use UserFrosting\Sprinkle\Core\Tests\Unit\Error\TestException;
use UserFrosting\Sprinkle\Core\Util\Message\Message;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * PrettyPageRenderer Test
 *
 * WARNING : This test validates the appropriates dependencies are called the
 * code doesn't throws any errors. The actual output is not fully tested.
 */
class PrettyPageRendererTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRender(): void
    {
        // Mocks
        $request = Mockery::mock(Request::class);
        $userMessage = new Message('title', 'description');

        $payload = [
            'title'       => 'title',
            'description' => 'description',
            'status'      => '234',
        ];

        // Mocks dependencies
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('error.pages.status')->once()->andReturn('pages/error/%d.html.twig')
            ->getMock();

        /** @var Mockery\MockInterface&Twig $twig */
        $twig = Mockery::mock(Twig::class)
            ->shouldReceive('fetch')->withArgs([
                'pages/error/234.html.twig',
                $payload,
            ])->once()->andReturn('body')
            ->getMock();

        // Create renderer and render exception
        $renderer = new PrettyPageRenderer(
            $config,
            $twig,
            new Run(),
            new PrettyPageHandler()
        );

        $data = $renderer->render(
            request: $request,
            exception: new TestException(),
            userMessage: $userMessage,
            statusCode: 234,
            displayErrorDetails: false
        );

        // Assert
        $this->assertSame('body', $data);
    }

    public function testRenderWithTwigRenderedException(): void
    {
        // Mocks
        $request = Mockery::mock(Request::class);
        $userMessage = new Message('title', 'description');

        $payload = [
            'title'       => 'title',
            'description' => 'description',
            'status'      => '234',
        ];

        // Mocks dependencies
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class);

        /** @var Mockery\MockInterface&Twig $twig */
        $twig = Mockery::mock(Twig::class)
            ->shouldReceive('fetch')->withArgs([
                'pages/error/warning.html.twig',
                $payload,
            ])->once()->andReturn('body')
            ->getMock();

        // Create renderer and render exception
        $renderer = new PrettyPageRenderer(
            $config,
            $twig,
            new Run(),
            new PrettyPageHandler()
        );

        $data = $renderer->render(
            request: $request,
            exception: new TwigTestException(),
            userMessage: $userMessage,
            statusCode: 234,
            displayErrorDetails: false
        );

        // Assert
        $this->assertSame('body', $data);
    }

    public function testRenderWithLoaderError(): void
    {
        // Mocks
        $request = Mockery::mock(Request::class);
        $userMessage = new Message('title', 'description');

        $payload = [
            'title'       => 'title',
            'description' => 'description',
            'status'      => '234',
        ];

        // Mocks dependencies
        /** @var Mockery\MockInterface&Config $config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('error.pages.status')->once()->andReturn('pages/error/%d.html.twig')
            ->shouldReceive('get')->with('error.pages.error')->once()->andReturn('pages/error/error.html.twig')
            ->getMock();

        /** @var Mockery\MockInterface&Twig $twig */
        $twig = Mockery::mock(Twig::class)
            ->shouldReceive('fetch')->withArgs(['pages/error/234.html.twig', $payload])->once()->andThrow(LoaderError::class)
            ->shouldReceive('fetch')->withArgs(['pages/error/error.html.twig', $payload])->once()->andReturn('body')
            ->getMock();

        // Create renderer and render exception
        $renderer = new PrettyPageRenderer(
            $config,
            $twig,
            new Run(),
            new PrettyPageHandler()
        );

        $data = $renderer->render(
            request: $request,
            exception: new TestException(),
            userMessage: $userMessage,
            statusCode: 234,
            displayErrorDetails: false
        );

        // Assert
        $this->assertSame('body', $data);
    }

    public function testRenderWithDisplayError(): void
    {
        // Mocks
        $request = Mockery::mock(Request::class);
        $userMessage = new Message('title', 'description');
        $exception = new TestException();

        // Mocks dependencies
        $config = Mockery::mock(Config::class);
        $twig = Mockery::mock(Twig::class);

        // Force Whoops handler to handle normally, bypassing CLI config
        $handler = new PrettyPageHandler();
        $handler->handleUnconditionally(true);

        // Force whoops config, so it doesn't echo and exit
        $whoops = new Run();
        $whoops->writeToOutput(false);
        $whoops->allowQuit(false);

        // Create renderer and render exception
        $renderer = new PrettyPageRenderer(
            $config,
            $twig,
            $whoops,
            $handler
        );

        $data = $renderer->render(
            request: $request,
            exception: $exception,
            userMessage: $userMessage,
            statusCode: 234,
            displayErrorDetails: true
        );

        // Assert
        $this->assertNotSame('', $data);
    }
}

class TwigTestException extends Exception implements TwigRenderedException
{
    public function getTemplate(): string
    {
        return 'pages/error/warning.html.twig';
    }
}
