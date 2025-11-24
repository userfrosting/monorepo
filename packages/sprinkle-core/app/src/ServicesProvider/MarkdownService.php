<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use League\CommonMark\ConverterInterface;
use League\CommonMark\Environment\Environment;
use League\CommonMark\MarkdownConverter;
use UserFrosting\Config\Config;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Markdown\MarkdownRepositoryInterface;
use UserFrosting\Sprinkle\Core\Markdown\SprinkleMarkdownRepository;

/**
 * Markdown service. Add CommonMark markdown parser with GitHub Flavored Markdown frontmatter support.
 *
 * Sprinkles can register custom markdown extensions by implementing the MarkdownExtensionRecipe interface.
 *
 * @see https://commonmark.thephpleague.com
 */
class MarkdownService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            ConverterInterface::class => function (Config $config, MarkdownRepositoryInterface $extensionLoader) {
                // Get markdown configuration from config service
                $markdownConfig = $config->get('markdown', []);

                $environment = new Environment($markdownConfig);

                // Register markdown extensions from sprinkles
                $this->registerMarkdownExtensions($environment, $extensionLoader);

                // Instantiate the converter engine and start converting some Markdown!
                $converter = new MarkdownConverter($environment);

                return $converter;
            },

            MarkdownRepositoryInterface::class => \DI\autowire(SprinkleMarkdownRepository::class),
        ];
    }

    /**
     * Register all Markdown Extensions defined in Sprinkles MarkdownExtensionRecipe.
     *
     * @param Environment                   $environment
     * @param MarkdownRepositoryInterface   $extensionLoader
     */
    protected function registerMarkdownExtensions(Environment $environment, MarkdownRepositoryInterface $extensionLoader): void
    {
        foreach ($extensionLoader as $extension) {
            $environment->addExtension($extension);
        }
    }
}
