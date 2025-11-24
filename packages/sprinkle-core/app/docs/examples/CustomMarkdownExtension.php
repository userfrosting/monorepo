<?php

declare(strict_types=1);

/**
 * Example: Adding Custom Markdown Extensions to UserFrosting
 * 
 * This example demonstrates how to extend the CommonMark markdown parser
 * with custom extensions in a UserFrosting Sprinkle.
 */

namespace MyApp\Sprinkle;

use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MarkdownExtensionRecipe;
use UserFrosting\Sprinkle\SprinkleRecipe;

/**
 * Example Sprinkle that adds custom markdown extensions.
 * 
 * To use this pattern:
 * 1. Implement MarkdownExtensionRecipe interface in your Sprinkle
 * 2. Return an array of extension class names from getMarkdownExtensions()
 * 3. The extensions will be automatically registered with the markdown parser
 */
class MySprinkle implements 
    SprinkleRecipe,
    MarkdownExtensionRecipe
{
    public function getName(): string
    {
        return 'My Custom Sprinkle';
    }

    public function getPath(): string
    {
        return __DIR__;
    }

    /**
     * Register custom markdown extensions.
     * 
     * These extensions will be added to the CommonMark parser environment
     * and will be available when converting markdown to HTML.
     * 
     * @return class-string<\League\CommonMark\Extension\ExtensionInterface>[]
     */
    public function getMarkdownExtensions(): array
    {
        return [
            // Add any CommonMark extension classes here
            TableExtension::class,
            StrikethroughExtension::class,
            // You can also add your own custom extension classes
            // MyCustomMarkdownExtension::class,
        ];
    }

    // ... other SprinkleRecipe methods ...
}

/**
 * Example of a custom markdown extension
 */
class MyCustomMarkdownExtension implements \League\CommonMark\Extension\ExtensionInterface
{
    public function register(\League\CommonMark\Environment\EnvironmentBuilderInterface $environment): void
    {
        // Register your custom parsers, renderers, etc.
        // Example: $environment->addInlineParser(new MyCustomParser());
        // Example: $environment->addRenderer(MyNode::class, new MyCustomRenderer());
    }
}

/**
 * Usage in Controllers or Services:
 * 
 * $converter = $this->ci->get(\League\CommonMark\ConverterInterface::class);
 * $markdown = '# Hello World';
 * $html = $converter->convert($markdown);
 * 
 * All extensions registered via MarkdownExtensionRecipe will be automatically
 * available in the converter.
 */
