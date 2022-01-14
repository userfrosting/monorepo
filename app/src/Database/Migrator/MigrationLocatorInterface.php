<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Support\ClassRepositoryInterface;

/**
 * Find and returns all migrations definitions (classes) registered and available.
 *
 * @extends ClassRepositoryInterface<MigrationInterface>
 */
interface MigrationLocatorInterface extends ClassRepositoryInterface
{
}
