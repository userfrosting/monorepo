<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Testing;

use Exception;
use Illuminate\Database\Connection;
use Psr\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Testing\TestCase;

/**
 * Trait o refresh database to fresh state.
 */
trait RefreshDatabase
{
    /**
     * Define hooks to migrate the database before and after each test.
     */
    public function refreshDatabase(): void
    {
        $this->usingInMemoryDatabase() ? $this->refreshInMemoryDatabase() : $this->refreshTestDatabase();
    }

    /**
     * Get the test case container.
     */
    private function getTestContainer(): ContainerInterface
    {
        if (!$this instanceof TestCase) {
            throw new Exception('CI/Container not available. Make sure you extend the correct TestCase');
        }

        return $this->getContainer();
    }

    /**
     * Determine if an in-memory database is being used.
     *
     * @return bool
     */
    public function usingInMemoryDatabase(): bool
    {
        /** @var Connection */
        $connection = $this->getTestContainer()->get(Connection::class);

        return $connection->getDatabaseName() === ':memory:';
    }

    /**
     * Refresh the in-memory database.
     */
    private function refreshInMemoryDatabase(): void
    {
        /** @var Migrator */
        $migrator = $this->getTestContainer()->get(Migrator::class);
        $migrator->migrate();
    }

    /**
     * Refresh a conventional test database.
     * Rollback all migrations and start over.
     */
    private function refreshTestDatabase(): void
    {
        /** @var Migrator */
        $migrator = $this->getTestContainer()->get(Migrator::class);
        $migrator->reset();
        $migrator->migrate();
    }
}
