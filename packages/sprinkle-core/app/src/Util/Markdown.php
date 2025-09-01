<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use League\CommonMark\ConverterInterface;
use UserFrosting\Config\Config;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Core\Exceptions\NotFoundException;
use UserFrosting\Sprinkle\Core\I18n\SiteLocaleInterface;
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Helper class to handle all things Markdown related.
 */
class Markdown
{
    public function __construct(
        protected ResourceLocatorInterface $locator,
        protected ConverterInterface $markdown,
        protected Config $config,
        protected Translator $translator,
        protected SiteLocaleInterface $siteLocale
    ) {
    }

    /**
     * Read a specific Markdown file.
     *
     * @param string $file The file to load, without extension.
     *
     * @return MarkdownFile
     */
    public function readFile(string $file): MarkdownFile
    {
        $path = $this->getFilePath($file);
        $content = $this->getFileContent($path);
        $result = $this->markdown->convert($content);

        // Retrieve the content and replace 'site' placeholders using the
        // translator service with data from the config service.
        $content = $result->getContent();
        $content = $this->translator->translate($content, ['site' => $this->config->get('site', [])]);

        $frontMatter = method_exists($result, 'getFrontMatter') ? $result->getFrontMatter() : [];

        return new MarkdownFile($content, $frontMatter);
    }

    /**
     * Attempt to load the localized resource file. If not found, fall back
     * to the default file. Throw a NotFoundException if neither file exists.
     *
     * @param string $file The file requested from the URI
     *
     * @return string
     */
    protected function getFilePath(string $file): string
    {
        $locale = $this->siteLocale->getLocaleIdentifier();

        // Remove the ".md" extension if present
        $file = rtrim($file, '.md');

        $path = $this->locator->getResource("markdown://{$file}.{$locale}.md");
        if ($path === null) {
            $path = $this->locator->getResource("markdown://{$file}.md");
            if ($path === null) {
                throw new NotFoundException();
            }
        }

        return $path->getAbsolutePath();
    }

    /**
     * Attempt to read the specified file.
     *
     * @param string $path
     *
     * @return string
     */
    protected function getFileContent(string $path): string
    {
        if (($content = @file_get_contents($path)) === false) {
            throw new FileNotFoundException("The file '$path' could not be read.");
        }

        return $content;
    }
}
