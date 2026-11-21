<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use DI\Attribute\Inject;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Twig\CacheHelper;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * ClearCache CLI Command.
 */
class ClearCacheCommand extends Command
{
    use WithSymfonyStyle;

    #[Inject]
    protected Cache $cache;

    #[Inject]
    protected Config $config;

    #[Inject]
    protected ResourceLocatorInterface $locator;

    #[Inject]
    protected CacheHelper $cacheHelper;

    #[Inject]
    protected Filesystem $filesystem;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('clean:cache')
             ->setAliases(['clear-cache'])
             ->setDescription('Clears the application cache. Includes cache service, Twig and Router cached data')
             ->setHelp('The `clear-cache` alias is deprecated. Use `clean:cache` instead.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Clearing cache');

        // Clear normal cache
        $this->io->writeln('<info> > Clearing Cache Instance</info>', OutputInterface::VERBOSITY_VERBOSE);
        $this->clearIlluminateCache();

        // Clear Twig cache
        $this->io->writeln('<info> > Clearing Twig Cached Data</info>', OutputInterface::VERBOSITY_VERBOSE);
        if (!$this->clearTwigCache()) {
            $this->io->error('Failed to clear Twig cached data. Make sure you have write access to the `app/cache/twig` directory.');

            return self::FAILURE;
        }

        // Clear router cache
        $this->io->writeln('<info> > Clearing Router cache file</info>', OutputInterface::VERBOSITY_VERBOSE);
        if (!$this->clearRouterCache()) {
            $file = $this->getRouteCacheFile();
            $this->io->error("Failed to delete Router cache file. Make sure you have write access to the `$file` file.");

            return self::FAILURE;
        }

        $this->io->success('Cache cleared !');

        return self::SUCCESS;
    }

    /**
     * Flush the cached data from the cache service.
     */
    public function clearIlluminateCache(): void
    {
        // @phpstan-ignore-next-line - False positive. PHPStan doesn't use the right class for cache.
        $this->cache->flush();
    }

    /**
     * Clear the Twig cache using the Twig CacheHelper class.
     *
     * @return bool true/false if operation is successful
     */
    public function clearTwigCache(): bool
    {
        return $this->cacheHelper->clearCache();
    }

    /**
     * Clear the Router cache data file.
     *
     * @return bool true/false if operation is successful
     */
    public function clearRouterCache(): bool
    {
        // Make sure file exist and delete it
        $file = $this->getRouteCacheFile();
        if ($this->filesystem->exists($file)) {
            return $this->filesystem->delete($file);
        }

        // It's still considered a success if file doesn't exist
        return true;
    }

    /**
     * Get the Router cache file path.
     *
     * @return string
     */
    protected function getRouteCacheFile(): string
    {
        $filename = $this->config->getString('cache.routerFile');
        $file = $this->locator->findResource("cache://$filename", true);

        return (string) $file;
    }
}
