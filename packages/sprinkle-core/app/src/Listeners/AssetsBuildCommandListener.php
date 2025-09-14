<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Listeners;

use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Bakery\Event\AssetsBuildCommandEvent;

/**
 * Event listener for the AssetsBuildCommand.
 */
class AssetsBuildCommandListener
{
    /**
     * @var string[] Commands to run with Webpack
     */
    protected array $webpackCommands = [
        'assets:install',
        'assets:webpack',
    ];

    /**
     * @var string[] Commands to run with Vite
     */
    protected array $viteCommands = [
        'assets:install',
    ];

    public function __construct(protected Config $config)
    {
    }

    /**
     * Handle the AssetsBuildCommandEvent event.
     *
     * @param AssetsBuildCommandEvent $event
     */
    public function __invoke(AssetsBuildCommandEvent $event): void
    {
        $bundler = $this->config->getString('assets.bundler', 'vite');
        $commands = match ($bundler) {
            'vite'    => $this->getViteCommands(),
            'webpack' => $this->getWebpackCommands(),
            default   => $this->getViteCommands(), // Fallback to Vite
        };

        $event->addCommands($commands);
    }

    /**
     * Get the list of commands to run when using Vite as the asset bundler.
     *
     * @return string[]
     */
    protected function getViteCommands(): array
    {
        // Enable the vite:build command if we are in production mode.
        // The "Build" should not run the Vite dev server.
        if ($this->config->getBool('assets.vite.dev', true) !== true) {
            if (!in_array('assets:vite', $this->viteCommands, true)) {
                $this->viteCommands[] = 'assets:vite';
            }
        }

        return $this->viteCommands;
    }

    /**
     * Get the list of commands to run when using Webpack as the asset bundler.
     *
     * @return string[]
     */
    protected function getWebpackCommands(): array
    {
        return $this->webpackCommands;
    }
}
