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

use UserFrosting\Sprinkle\Core\Event\ResourceLocatorInitiatedEvent;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Register the required streams with the resource locator.
 */
class ResourceLocatorInitiated
{
    /**
     * @param SprinkleManager $sprinkleManager
     */
    public function __construct(
        protected SprinkleManager $sprinkleManager,
    ) {
    }

    /**
     * Add all defined streams.
     *
     * @param ResourceLocatorInitiatedEvent $event
     */
    public function __invoke(ResourceLocatorInitiatedEvent $event): void
    {
        foreach ($this->getStreams() as $stream) {
            $event->locator->addStream($stream);
        }
    }

    /**
     * Returns all ResourceStream to register.
     *
     * @return ResourceStream[]
     */
    protected function getStreams(): array
    {
        $publicPath = $this->sprinkleManager->getMainSprinkle()->getPath() . '../public';

        return [
            new ResourceStream('sprinkles', path: ''),
            new ResourceStream('config'),
            new ResourceStream('extra'),
            new ResourceStream('locale'),
            new ResourceStream('schema'),
            new ResourceStream('templates'),

            // Add readonly streams
            new ResourceStream('markdown', readonly: true),

            // Add shared streams
            new ResourceStream('cache', shared: true),
            new ResourceStream('database', shared: true),
            new ResourceStream('logs', shared: true),
            new ResourceStream('sessions', shared: true),
            new ResourceStream('storage', shared: true),

            // Add shared public stream & an alias for the asset stream
            new ResourceStream('public', path: $publicPath, shared: true),
            new ResourceStream('assets', path: 'public://assets', shared: true),
        ];
    }
}
