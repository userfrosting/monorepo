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

use ArrayIterator;
use DI\Container;
use League\CommonMark\ConverterInterface;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Markdown\MarkdownRepositoryInterface;
use UserFrosting\Sprinkle\Core\ServicesProvider\MarkdownService;
use UserFrosting\Testing\ContainerStub;

/**
 * Unit tests for MarkdownService.
 * Verify that the service properly uses config to customize markdown parser behavior.
 */
class MarkdownServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Container $ci;

    public function setUp(): void
    {
        parent::setUp();

        // Create container with provider to test
        $provider = new MarkdownService();
        $this->ci = ContainerStub::create($provider->register());
    }

    /**
     * Helper method to mock MarkdownRepositoryInterface with core extensions.
     * These are the minimum extensions needed for basic markdown conversion.
     */
    protected function mockEmptyMarkdownRepository(): void
    {
        // Create instances of the core extensions
        $extensions = [
            new CommonMarkCoreExtension(),
            new FrontMatterExtension(),
            new GithubFlavoredMarkdownExtension(),
        ];

        $repository = Mockery::mock(MarkdownRepositoryInterface::class)
            ->shouldReceive('getIterator')->once()->andReturn(new ArrayIterator($extensions))
            ->getMock();
        $this->ci->set(MarkdownRepositoryInterface::class, $repository);
    }

    public function testConverterWithDefaultConfig(): void
    {
        // Set mock Config with default markdown config
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')
            ->with('markdown', [])
            ->once()
            ->andReturn([
                'html_input'         => 'strip',
                'allow_unsafe_links' => false,
                'max_nesting_level'  => 100,
            ])
            ->getMock();
        
        $this->ci->set(Config::class, $config);

        $this->mockEmptyMarkdownRepository();
        
        // Get the converter
        $converter = $this->ci->get(ConverterInterface::class);
        
        // Verify it's a ConverterInterface
        $this->assertInstanceOf(ConverterInterface::class, $converter);
    }

    public function testConverterWithEmptyConfig(): void
    {
        // Set mock Config that returns empty array
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')
            ->with('markdown', [])
            ->once()
            ->andReturn([])
            ->getMock();
        
        $this->ci->set(Config::class, $config);

        $this->mockEmptyMarkdownRepository();
        
        // Get the converter - should still work with empty config
        $converter = $this->ci->get(ConverterInterface::class);
        
        $this->assertInstanceOf(ConverterInterface::class, $converter);
    }

    public function testConverterWithCustomConfig(): void
    {
        // Set mock Config with custom markdown config
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')
            ->with('markdown', [])
            ->once()
            ->andReturn([
                'html_input'         => 'allow',
                'allow_unsafe_links' => true,
                'max_nesting_level'  => 50,
            ])
            ->getMock();
        
        $this->ci->set(Config::class, $config);

        $this->mockEmptyMarkdownRepository();
        
        // Get the converter
        $converter = $this->ci->get(ConverterInterface::class);
        
        $this->assertInstanceOf(ConverterInterface::class, $converter);
    }

    public function testConverterCanConvertBasicMarkdown(): void
    {
        // Set mock Config
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')
            ->with('markdown', [])
            ->once()
            ->andReturn([])
            ->getMock();
        
        $this->ci->set(Config::class, $config);

        $this->mockEmptyMarkdownRepository();
        
        // Get the converter
        $converter = $this->ci->get(ConverterInterface::class);
        
        // Test basic markdown conversion
        $result = $converter->convert('# Hello World');
        $this->assertStringContainsString('Hello World', (string) $result);
    }

    public function testConverterHandlesHtmlAccordingToConfig(): void
    {
        // Test with html_input set to 'strip'
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')
            ->with('markdown', [])
            ->once()
            ->andReturn([
                'html_input' => 'strip',
            ])
            ->getMock();
        
        $this->ci->set(Config::class, $config);

        $this->mockEmptyMarkdownRepository();
        
        $converter = $this->ci->get(ConverterInterface::class);
        
        // Convert markdown with HTML
        $result = $converter->convert('Hello <script>alert("xss")</script> World');
        $output = (string) $result;
        
        // With 'strip', the script tag should be removed
        $this->assertStringNotContainsString('<script>', $output);
    }
}
