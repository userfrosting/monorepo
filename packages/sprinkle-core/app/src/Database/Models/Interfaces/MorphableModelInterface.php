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

/**
 * Defines the contract required by an Eloquent model used in a
 * polymorphic relationship.
 *
 * Eloquent's `Model` is a concrete class rather than an interface, so
 * UserFrosting model interfaces such as `UserInterface` and
 * `GroupInterface` cannot extend it. This interface provides a shared type
 * for those models and exposes the methods needed to resolve their
 * polymorphic type and key. This interface can be used to type-hint and
 * type validate any argument requiring an Eloquent model to be used as a
 * polymorphic relation target.
 */
interface MorphableModelInterface
{
    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey();

    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass();
}
