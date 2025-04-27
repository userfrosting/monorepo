<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Twig\Extensions;

use donatj\UserAgent\UserAgentParser;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
use UserFrosting\Sprinkle\Core\Twig\Extensions\UserAgentExtension;

class UserAgentExtensionTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/18.3.1 Safari/605.1.15';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    public function testGetGlobals(): void
    {
        // Setup Extension & get globals
        $parser = new UserAgentParser();
        $extension = new UserAgentExtension($parser);
        $globals = $extension->getGlobals();

        $this->assertArrayHasKey('user_agent', $globals);
        $this->assertIsArray($globals['user_agent']);
        $this->assertArrayHasKey('ip', $globals['user_agent']);
        $this->assertArrayHasKey('browser', $globals['user_agent']);
        $this->assertArrayHasKey('version', $globals['user_agent']);
        $this->assertArrayHasKey('platform', $globals['user_agent']);

        $userAgent = $globals['user_agent'];
        $this->assertSame('127.0.0.1', $userAgent['ip']);
        $this->assertSame('Safari', $userAgent['browser']);
        $this->assertSame('18.3.1', $userAgent['version']);
        $this->assertSame('Macintosh', $userAgent['platform']);
    }

    public function testTwigTemplate(): void
    {
        // Create and add to extensions.
        $parser = new UserAgentParser();
        $extension = new UserAgentExtension($parser);

        // Create dumb Twig and test adding extension
        $view = Twig::create('');
        $view->addExtension($extension);

        // Assert each global variables
        $this->assertSame('127.0.0.1', $view->fetchFromString('{{user_agent.ip}}'));
        $this->assertSame('Safari', $view->fetchFromString('{{user_agent.browser}}'));
        $this->assertSame('18.3.1', $view->fetchFromString('{{user_agent.version}}'));
        $this->assertSame('Macintosh', $view->fetchFromString('{{user_agent.platform}}'));
    }
}
