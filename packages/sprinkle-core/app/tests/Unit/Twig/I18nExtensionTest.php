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
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\I18n\SiteLocaleInterface;
use UserFrosting\Sprinkle\Core\Twig\Extensions\I18nExtension;

/**
 * Tests for TwigI18nExtension class.
 * Tests Translate twig extensions
 */
class I18nExtensionTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testTranslateIntegration(): void
    {
        /** @var Mockery\MockInterface&Translator */
        $translator = Mockery::mock(Translator::class)
            ->shouldReceive('translate')
            ->with('USER', 2)
            ->once()
            ->andReturn('foobar')
            ->getMock();

        /** @var SiteLocaleInterface */
        $siteLocale = Mockery::mock(SiteLocaleInterface::class)
            ->shouldReceive('getLocaleIdentifier')->andReturn('fr_FR')
            ->shouldReceive('getLocaleConfig')->andReturn([
                'name'        => 'French',
                'regional'    => 'Français',
                'authors'     => ['Me'],
                'plural_rule' => 2,
                'tag'         => 'fr-FR',
                'parents'     => ['en_US'],
            ])
            ->getMock();

        // Create and add to extensions.
        $extensions = new I18nExtension($translator, $siteLocale);

        // Create dumb Twig and test adding extension
        $view = Twig::create('');
        $view->addExtension($extensions);

        $result = $view->fetchFromString('{{ translate("USER", 2) }}');
        $this->assertSame('foobar', $result);

        $result = $view->fetchFromString('{{ currentLocale }}');
        $this->assertSame('fr_FR', $result);

        $result = $view->fetchFromString('{{ currentLocaleConfig.tag }}');
        $this->assertSame('fr-FR', $result);
    }
}
