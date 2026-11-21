<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery\Event;

use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\PrunableModelInterface;

/**
 * Event dispatched to collect models to prune during database cleanup.
 */
final class DatabasePruneEvent
{
    /**
     * @param array<class-string<PrunableModelInterface>> $models
     */
    public function __construct(protected array $models = [])
    {
    }

    /**
     * @return array<class-string<PrunableModelInterface>>
     */
    public function getModels(): array
    {
        return $this->models;
    }

    /**
     * @param class-string<PrunableModelInterface> $model
     */
    public function addModel(string $model): void
    {
        $this->models[] = $model;
    }
}
