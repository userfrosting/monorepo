<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Markdown;

use League\CommonMark\Extension\ExtensionInterface;
use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Sprinkle\Recipe\MarkdownExtensionRecipe;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\Support\ClassRepository;
use UserFrosting\Support\Exception\BadClassNameException;
use UserFrosting\Support\Exception\BadInstanceOfException;

/**
 * Find and returns all registered ExtensionInterface across all sprinkles, using MarkdownExtensionRecipe.
 *
 * @extends ClassRepository<ExtensionInterface>
 */
final class SprinkleMarkdownRepository extends ClassRepository implements MarkdownRepositoryInterface
{
    /**
     * @param SprinkleManager    $sprinkleManager
     * @param ContainerInterface $ci
     */
    public function __construct(
        protected SprinkleManager $sprinkleManager,
        protected ContainerInterface $ci
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function all(): array
    {
        $instances = [];

        foreach ($this->sprinkleManager->getSprinkles() as $sprinkle) {
            if (!$sprinkle instanceof MarkdownExtensionRecipe) {
                continue;
            }
            foreach ($sprinkle->getMarkdownExtensions() as $extensionClass) {
                if (!class_exists($extensionClass)) {
                    throw new BadClassNameException("Extension class `$extensionClass` not found.");
                }
                $instance = $this->ci->get($extensionClass);
                if (!is_object($instance) || !is_subclass_of($instance, ExtensionInterface::class)) {
                    throw new BadInstanceOfException("Extension class `$extensionClass` doesn't implement " . ExtensionInterface::class . '.');
                }
                $instances[] = $instance;
            }
        }

        return $instances;
    }
}
