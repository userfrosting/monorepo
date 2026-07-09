<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Controller;

use League\CommonMark\ConverterInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\Exceptions\NotFoundException;
use UserFrosting\Sprinkle\Core\I18n\SiteLocaleInterface;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Sprinkle\Core\Util\Markdown;
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\UniformResourceLocator\ResourceInterface;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class MarkdownTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    public function testBasicFile(): void
    {
        $file = 'about';
        $locale = 'en_US';
        $markdownContent = '<p>Lorem <strong>ipsum</strong> dolor</p>' . PHP_EOL;
        $frontMatter = [];
        $config = ['name' => 'TestSite'];

        /** @var ResourceInterface */
        $mockResource = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getAbsolutePath')->once()->andReturn(__DIR__ . "/data/{$file}.md")
            ->getMock();

        /** @var ResourceLocatorInterface */
        $mockLocator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')->with("markdown://{$file}.{$locale}.md")->once()->andReturn(null)
            ->shouldReceive('getResource')->with("markdown://{$file}.md")->once()->andReturn($mockResource)
            ->getMock();

        /** @var Config */
        $mockConfig = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('site', [])->once()->andReturn($config)
            ->getMock();

        /** @var Translator */
        $mockTranslator = Mockery::mock(Translator::class)
            ->shouldReceive('translate')->with($markdownContent, ['site' => $config])->once()->andReturn($markdownContent)
            ->getMock();

        /** @var SiteLocaleInterface */
        $mockSiteLocale = Mockery::mock(SiteLocaleInterface::class)
            ->shouldReceive('getLocaleIdentifier')->once()->andReturn($locale)
            ->getMock();

        // Get the real converter
        $converter = $this->ci->get(ConverterInterface::class);

        $markdown = new Markdown(
            $mockLocator,
            $converter,
            $mockConfig,
            $mockTranslator,
            $mockSiteLocale
        );

        $result = $markdown->readFile($file);
        $this->assertEquals($markdownContent, $result->content);
        $this->assertEquals($frontMatter, $result->frontMatter);
    }

    public function testCaseInsensitivePath(): void
    {
        $file = 'about'; // lowercase filename on disk
        $locale = 'en_US';
        $markdownContent = '<p>Lorem <strong>ipsum</strong> dolor</p>' . PHP_EOL;
        $config = ['name' => 'TestSite'];

        /** @var ResourceInterface */
        $mockResource = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getAbsolutePath')->once()->andReturn(__DIR__ . "/data/{$file}.md")
            ->getMock();

        // Input "About" is normalized to "about" before lookup, exact match succeeds.
        /** @var ResourceLocatorInterface */
        $mockLocator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')->with("markdown://{$file}.{$locale}.md")->once()->andReturn(null)
            ->shouldReceive('getResource')->with("markdown://{$file}.md")->once()->andReturn($mockResource)
            ->getMock();

        /** @var Config */
        $mockConfig = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('site', [])->once()->andReturn($config)
            ->getMock();

        /** @var Translator */
        $mockTranslator = Mockery::mock(Translator::class)
            ->shouldReceive('translate')->with($markdownContent, ['site' => $config])->once()->andReturn($markdownContent)
            ->getMock();

        /** @var SiteLocaleInterface */
        $mockSiteLocale = Mockery::mock(SiteLocaleInterface::class)
            ->shouldReceive('getLocaleIdentifier')->once()->andReturn($locale)
            ->getMock();

        $converter = $this->ci->get(ConverterInterface::class);

        $markdown = new Markdown(
            $mockLocator,
            $converter,
            $mockConfig,
            $mockTranslator,
            $mockSiteLocale
        );

        // URL "About" normalizes to "about", finds "about.md" via exact match.
        $result = $markdown->readFile('About');
        $this->assertEquals($markdownContent, $result->content);
    }

    public function testCaseInsensitivePathUppercaseFile(): void
    {
        $file = 'tos'; // normalized input
        $locale = 'en_US';
        $markdownContent = '<p>Terms</p>' . PHP_EOL;
        $config = ['name' => 'TestSite'];

        /** @var ResourceInterface */
        $mockResource = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getBasename')->once()->andReturn('Tos.md') // file on disk has uppercase
            ->shouldReceive('getAbsolutePath')->once()->andReturn(__DIR__ . '/data/about.md') // reuse existing file
            ->getMock();

        // Exact match fails (file is "Tos.md" not "tos.md"), fallback via listResources.
        /** @var ResourceLocatorInterface */
        $mockLocator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')->with("markdown://{$file}.{$locale}.md")->once()->andReturn(null)
            ->shouldReceive('getResource')->with("markdown://{$file}.md")->once()->andReturn(null)
            ->shouldReceive('listResources')->with('markdown://', true)->once()->andReturn([$mockResource])
            ->getMock();

        /** @var Config */
        $mockConfig = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('site', [])->once()->andReturn($config)
            ->getMock();

        /** @var Translator */
        $mockTranslator = Mockery::mock(Translator::class)
            ->shouldReceive('translate')->once()->andReturn($markdownContent)
            ->getMock();

        /** @var SiteLocaleInterface */
        $mockSiteLocale = Mockery::mock(SiteLocaleInterface::class)
            ->shouldReceive('getLocaleIdentifier')->once()->andReturn($locale)
            ->getMock();

        $converter = $this->ci->get(ConverterInterface::class);

        $markdown = new Markdown(
            $mockLocator,
            $converter,
            $mockConfig,
            $mockTranslator,
            $mockSiteLocale
        );

        // URL "Tos" normalizes to "tos", exact match misses "Tos.md",
        // case-insensitive fallback finds it.
        $result = $markdown->readFile('Tos');
        $this->assertEquals($markdownContent, $result->content);
    }

    public function testCaseInsensitivePathUppercaseLocalizedFile(): void
    {
        $file = 'tos'; // normalized input
        $locale = 'fr';
        $markdownContent = '<p>Lorem <strong>ipsum</strong> dolor</p>' . PHP_EOL;
        $config = ['name' => 'TestSite'];

        /** @var ResourceInterface */
        $defaultResource = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getBasename')->once()->andReturn('Tos.md')
            ->shouldReceive('getAbsolutePath')->once()->andReturn(__DIR__ . '/data/notexistingfile.md')
            ->getMock();

        /** @var ResourceInterface */
        $localizedResource = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getBasename')->once()->andReturn('Tos.fr.md')
            ->shouldReceive('getAbsolutePath')->once()->andReturn(__DIR__ . '/data/about.md')
            ->getMock();

        // Exact match fails, fallback scan finds both; localized match should win.
        /** @var ResourceLocatorInterface */
        $mockLocator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')->with("markdown://{$file}.{$locale}.md")->once()->andReturn(null)
            ->shouldReceive('getResource')->with("markdown://{$file}.md")->once()->andReturn(null)
            ->shouldReceive('listResources')->with('markdown://', true)->once()->andReturn([$defaultResource, $localizedResource])
            ->getMock();

        /** @var Config */
        $mockConfig = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('site', [])->once()->andReturn($config)
            ->getMock();

        /** @var Translator */
        $mockTranslator = Mockery::mock(Translator::class)
            ->shouldReceive('translate')->with($markdownContent, ['site' => $config])->once()->andReturn($markdownContent)
            ->getMock();

        /** @var SiteLocaleInterface */
        $mockSiteLocale = Mockery::mock(SiteLocaleInterface::class)
            ->shouldReceive('getLocaleIdentifier')->once()->andReturn($locale)
            ->getMock();

        $converter = $this->ci->get(ConverterInterface::class);

        $markdown = new Markdown(
            $mockLocator,
            $converter,
            $mockConfig,
            $mockTranslator,
            $mockSiteLocale
        );

        $result = $markdown->readFile('Tos');
        $this->assertEquals($markdownContent, $result->content);
    }

    public function testLocalizedFileWithFrontMatter(): void
    {
        $file = 'test'; // case not matching + md extension (later)
        $locale = 'fr'; // locale identifier
        $markdownContent = '<p>Ceci est un <strong>test</strong> fonctionnel.</p>' . PHP_EOL;
        $frontMatter = [
            'title' => 'Le Foo',
            'tag'   => 'Le test',
        ];
        $config = ['name' => 'TestSite'];

        /** @var ResourceInterface */
        $mockResource = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getAbsolutePath')->once()->andReturn(__DIR__ . "/data/{$file}.{$locale}.md")
            ->getMock();

        /** @var ResourceLocatorInterface */
        $mockLocator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')->with("markdown://{$file}.{$locale}.md")->once()->andReturn($mockResource)
            ->shouldNotReceive('getResource')->with("markdown://{$file}.md")
            ->getMock();

        /** @var Config */
        $mockConfig = Mockery::mock(Config::class)
            ->shouldReceive('get')->with('site', [])->once()->andReturn($config)
            ->getMock();

        /** @var Translator */
        $mockTranslator = Mockery::mock(Translator::class)
            ->shouldReceive('translate')->with($markdownContent, ['site' => $config])->once()->andReturn($markdownContent)
            ->getMock();

        /** @var SiteLocaleInterface */
        $mockSiteLocale = Mockery::mock(SiteLocaleInterface::class)
            ->shouldReceive('getLocaleIdentifier')->once()->andReturn($locale)
            ->getMock();

        // Get the real converter
        $converter = $this->ci->get(ConverterInterface::class);

        $markdown = new Markdown(
            $mockLocator,
            $converter,
            $mockConfig,
            $mockTranslator,
            $mockSiteLocale
        );

        $result = $markdown->readFile($file . '.md');
        $this->assertEquals($markdownContent, $result->content);
        $this->assertEquals($frontMatter, $result->frontMatter);
    }

    public function testFileNotFound(): void
    {
        $file = 'about';
        $locale = 'en_US';

        /** @var ResourceLocatorInterface */
        $mockLocator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')->with("markdown://{$file}.{$locale}.md")->once()->andReturn(null)
            ->shouldReceive('getResource')->with("markdown://{$file}.md")->once()->andReturn(null)
            ->shouldReceive('listResources')->with('markdown://', true)->once()->andReturn([])
            ->getMock();

        /** @var Config */
        $mockConfig = Mockery::mock(Config::class);

        /** @var Translator */
        $mockTranslator = Mockery::mock(Translator::class);

        /** @var SiteLocaleInterface */
        $mockSiteLocale = Mockery::mock(SiteLocaleInterface::class)
            ->shouldReceive('getLocaleIdentifier')->once()->andReturn($locale)
            ->getMock();

        // Get the real converter
        $converter = $this->ci->get(ConverterInterface::class);

        $markdown = new Markdown(
            $mockLocator,
            $converter,
            $mockConfig,
            $mockTranslator,
            $mockSiteLocale
        );

        $this->expectException(NotFoundException::class);
        $markdown->readFile($file);
    }

    public function testFileNotFoundException(): void
    {
        $file = 'notexistingfile';
        $locale = 'en_US';

        /** @var ResourceInterface */
        $mockResource = Mockery::mock(ResourceInterface::class)
            ->shouldReceive('getAbsolutePath')->once()->andReturn(__DIR__ . "/data/{$file}.md")
            ->getMock();

        /** @var ResourceLocatorInterface */
        $mockLocator = Mockery::mock(ResourceLocatorInterface::class)
            ->shouldReceive('getResource')->with("markdown://{$file}.{$locale}.md")->once()->andReturn(null)
            ->shouldReceive('getResource')->with("markdown://{$file}.md")->once()->andReturn($mockResource)
            ->getMock();

        /** @var Config */
        $mockConfig = Mockery::mock(Config::class)
            ->shouldNotReceive('get')
            ->getMock();

        /** @var Translator */
        $mockTranslator = Mockery::mock(Translator::class)
            ->shouldNotReceive('translate')
            ->getMock();

        /** @var SiteLocaleInterface */
        $mockSiteLocale = Mockery::mock(SiteLocaleInterface::class)
            ->shouldReceive('getLocaleIdentifier')->once()->andReturn($locale)
            ->getMock();

        // Get the real converter
        $converter = $this->ci->get(ConverterInterface::class);

        $markdown = new Markdown(
            $mockLocator,
            $converter,
            $mockConfig,
            $mockTranslator,
            $mockSiteLocale
        );

        $this->expectException(FileNotFoundException::class);
        $markdown->readFile($file);
    }
}
