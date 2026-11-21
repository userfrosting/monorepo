<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models\Interfaces;

use Illuminate\Database\Eloquent\Builder;

/**
 * Identifies models that define a pruning query.
 */
interface PrunableModelInterface
{
    /**
     * Get the model query used to select records for pruning.
     *
     * @return Builder
     */
    public function prunable(): Builder;

    /**
     * Prune all matching records in batches.
     */
    public function pruneAll(int $chunkSize = 1000): int;
}
