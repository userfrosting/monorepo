<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit\Twig;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Slim\Views\Twig;
use UserFrosting\Config\Config;

use UserFrosting\Sprinkle\Core\Twig\Extensions\CoreExtension;

/**
 * CoreExtensionTest class.
 * Tests Core twig extensions
 */
class CoreExtensionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Twig $view;

    public function setUp(): void
    {
        parent::setUp();

        /** @var Mockery\MockInterface&Config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('site')->andReturn(['foo' => 'bar'])
            ->getMock();

        // Create and add to extensions.
        $extensions = new CoreExtension($config);

        // Create dumb Twig and test adding extension
        $this->view = Twig::create('');
        $this->view->addExtension($extensions);
    }

    public function testPhoneFilter(): void
    {
        $result = $this->view->fetchFromString('{{ data|phone }}', ['data' => '5551234567']);
        $this->assertSame('(555) 123-4567', $result);
    }

    public function testUnescapeFilter(): void
    {
        $string = "I'll \"walk\" the <b>dog</b> now";
        $this->assertNotSame($string, $this->view->fetchFromString('{{ foo }}', ['foo' => htmlentities($string)]));
        $this->assertNotSame($string, $this->view->fetchFromString('{{ foo|unescape }}', ['foo' => htmlentities($string)]));
        $this->assertNotSame($string, $this->view->fetchFromString('{{ foo|raw }}', ['foo' => htmlentities($string)]));
        $this->assertSame($string, $this->view->fetchFromString('{{ foo|unescape|raw }}', ['foo' => htmlentities($string)]));
    }

    public function testGlobal(): void
    {
        $this->assertSame('bar', $this->view->fetchFromString('{{ site.foo }}'));
    }
}
