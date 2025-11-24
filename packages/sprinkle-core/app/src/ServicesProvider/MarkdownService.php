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
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use UserFrosting\Config\Config;
use UserFrosting\ServicesProvider\ServicesProviderInterface;

/**
 * Markdown service. Add CommonMark markdown parser with GitHub Flavored Markdown frontmatter support.
 *
 * @see https://commonmark.thephpleague.com
 * 
 * TODO : Should have a way to extend the markdown parser with custom extensions.
 */
class MarkdownService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            ConverterInterface::class => function (Config $config) {
                // Get markdown configuration from config service
                $markdownConfig = $config->get('markdown', []);

                $environment = new Environment($markdownConfig);
                $environment->addExtension(new CommonMarkCoreExtension());
                $environment->addExtension(new FrontMatterExtension());
                $environment->addExtension(new GithubFlavoredMarkdownExtension());

                // Instantiate the converter engine and start converting some Markdown!
                $converter = new MarkdownConverter($environment);

                return $converter;
            },
        ];
    }
}
