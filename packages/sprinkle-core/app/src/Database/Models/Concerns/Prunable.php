<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Adds batched pruning to models with a configured `prunable` query.
 */
trait Prunable
{
    /**
     * Prune all matching records in batches.
     */
    public function pruneAll(int $chunkSize = 1000): int
    {
        $total = 0;

        $this->prunable()->chunkById($chunkSize, function ($models) use (&$total): void {
            $ids = $models->modelKeys();
            $total += count($ids);

            $this->newModelQuery()->whereKey($ids)->delete();
        });

        return $total;
    }

    /**
     * Get the model query used to select records for pruning.
     *
     * @return Builder
     */
    abstract public function prunable(): Builder;
}
