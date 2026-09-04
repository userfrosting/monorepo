<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\I18n;

use Illuminate\Support\Arr;
use LogicException;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;
use UserFrosting\Support\Repository\Loader\FileRepositoryLoader;
use UserFrosting\Support\Repository\Loader\YamlFileLoader;
use UserFrosting\Support\Repository\Repository;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Load all locale all "Key => translation" data matrix.
 */
class Dictionary extends Repository implements DictionaryInterface
{
    /**
     * @var string Base URI for locator
     */
    protected string $uri = 'locale://';

    /**
     * @var FileRepositoryLoader
     */
    protected FileRepositoryLoader $fileLoader;

    /**
     * @var mixed[] Locale "Key => translation" data matrix cache
     */
    protected $items = [];

    /**
     * @param LocaleInterface           $locale
     * @param ResourceLocatorInterface  $locator
     * @param FileRepositoryLoader|null $fileLoader File loader used to load YAML/JSON dictionary files (default to YAML Loader)
     */
    public function __construct(
        protected LocaleInterface $locale,
        protected ResourceLocatorInterface $locator,
        ?FileRepositoryLoader $fileLoader = null
    ) {
        $this->fileLoader = $fileLoader ?? new YamlFileLoader();

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getDictionary(): array
    {
        if (count($this->items) === 0) {
            $this->items = $this->loadDictionary();
        }

        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlattenDictionary(): array
    {
        return Arr::dot($this->getDictionary());
    }

    /**
     * Set the locator base URI (default 'locale://').
     *
     * @param string $uri
     */
    public function setUri(string $uri): void
    {
        $this->uri = $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale(): LocaleInterface
    {
        return $this->locale;
    }

    /**
     * Return the file repository loader used to load.
     *
     * @return FileRepositoryLoader
     */
    public function getFileLoader(): FileRepositoryLoader
    {
        return $this->fileLoader;
    }

    /**
     * Load the dictionary from file.
     *
     * @return mixed[] The locale dictionary
     */
    protected function loadDictionary(): array
    {
        $dictionary = [];

        // List of loaded locales
        $loadedLocale = [$this->locale->getIdentifier()];

        // Get list of files to load
        $files = $this->getFiles();
        $files = $this->filterDictionaryFiles($files);
        $files = array_map('strval', $files);

        // Load all files content if files are present
        if (count($files) !== 0) {
            $phpFiles = array_filter($files, fn (string $file): bool => pathinfo($file, PATHINFO_EXTENSION) === 'php');
            $dataFiles = array_diff($files, $phpFiles);

            if (count($phpFiles) !== 0) {
                $loader = $this->fileLoader instanceof ArrayFileLoader
                    ? $this->fileLoader
                    : new ArrayFileLoader();
                $loader->setPaths($phpFiles);
                $dictionary = $loader->load();
            }

            if (count($dataFiles) !== 0) {
                $loader = $this->getFileLoader();
                $loader->setPaths($dataFiles);
                $dictionary = array_replace_recursive($dictionary, $loader->load());
            }
        }

        // Now load dependent dictionaries
        foreach ($this->locale->getDependentLocales() as $locale) {
            // Stop if locale already loaded to prevent recursion
            $localesToLoad = array_merge([$locale->getIdentifier()], $locale->getDependentLocalesIdentifier());
            $intersection = array_intersect($localesToLoad, $loadedLocale);
            if (count($intersection) !== 0) {
                throw new LogicException("Can't load dictionary. Dependencies recursion detected : " . implode(', ', $intersection));
            }

            $dependentDictionary = new self($locale, $this->locator, $this->fileLoader);
            $dictionary = array_replace_recursive($dependentDictionary->getDictionary(), $dictionary);

            $loadedLocale[] = $locale->getIdentifier();
        }

        return $dictionary;
    }

    /**
     * Remove config files from locator results and convert ResourceInterface to path/string.
     *
     * @param \UserFrosting\UniformResourceLocator\ResourceInterface[] $files
     *
     * @return string[]
     */
    protected function filterDictionaryFiles(array $files): array
    {
        // @phpstan-ignore-next-line False positive. ResourceInterface is Stringable.
        return array_filter($files, function ($file) {
            $extension = $file->getExtension();
            if ($extension === 'php') {
                return (string) $file;
            }

            if (in_array($extension, ['json', 'yaml', 'yml'], true) && pathinfo((string) $file, PATHINFO_FILENAME) !== 'locale') {
                return (string) $file;
            }

            return false;
        });
    }

    /**
     * List all files for a given locale using the locator.
     *
     * @return \UserFrosting\UniformResourceLocator\ResourceInterface[]
     */
    protected function getFiles(): array
    {
        $resources = $this->locator->listResources($this->uri . $this->locale->getIdentifier(), true, false);
        $resources = array_reverse($resources);

        return $resources;
    }
}
