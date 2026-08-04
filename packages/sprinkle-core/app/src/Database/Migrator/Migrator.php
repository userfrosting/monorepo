<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationRollbackException;

/**
 * Migrator utility used to manage and run database migrations.
 */
class Migrator
{
    /**
     * @param MigrationRepositoryInterface $repository The migration repository
     * @param MigrationLocatorInterface    $locator    The Migration locator
     * @param Capsule                      $db         The database
     */
    public function __construct(
        protected MigrationRepositoryInterface $repository,
        protected MigrationLocatorInterface $locator,
        protected Capsule $db,
    ) {
    }

    /**
     * Get installed migrations.
     *
     * @return class-string[] An array of migration class names in the order they where ran.
     */
    public function getInstalled(): array
    {
        return $this->repository->list();
    }

    /**
     * Get migrations available and registered to be run.
     *
     * @return class-string[] An array of migration class names.
     */
    public function getAvailable(): array
    {
        return $this->locator->list();
    }

    /**
     * Returns migration that are installed, but not available.
     *
     * @return class-string[] An array of migration class names.
     */
    public function getStale(): array
    {
        $stale = array_diff($this->getInstalled(), $this->getAvailable());

        return array_values($stale);
    }

    /**
     * Returns migration that are available, but not installed, in the order
     * they need to be run.
     *
     * @return class-string[] An array of migration class names.
     */
    public function getPending(): array
    {
        $pending = array_diff($this->getAvailable(), $this->getInstalled());

        $migrations = [];

        // We need to validate the dependencies of each pending migration.
        foreach ($pending as $migration) {
            $dependencies = $this->getPendingDependencies($migration);
            $migrations = array_merge($migrations, $dependencies);
        }

        // Remove duplicate and re-index.
        // Duplicate migrations will be removed, preserving the correct order.
        $migrations = array_unique($migrations);
        $migrations = array_values($migrations);

        return $migrations;
    }

    /**
     * We run across all step even if it's not required, as this is run
     * recursively across all dependencies.
     *
     * @param class-string $migrationClass
     *
     * @return class-string[] This migration and it's dependencies.
     */
    protected function getPendingDependencies(string $migrationClass): array
    {
        // Start with the migration in the return.
        // This allows the main migration to be placed in the correct order
        // When doing a nested call.
        $dependencies = [$migrationClass];

        // Loop dependencies and validate them
        foreach ($this->getDependenciesProperty($migrationClass) as $dependency) {
            // The dependency might already be installed. Check that first.
            // Skip if it's the case (we don't want it pending again).
            // This allows to accept stale (installed, but not available
            // migration) as valid dependencies.
            if (in_array($dependency, $this->getInstalled(), true)) {
                continue;
            }

            // Make sure dependency exist. Otherwise it's a dead end.
            if (!$this->locator->has($dependency)) {
                throw new MigrationDependencyNotMetException("$migrationClass depends on $dependency, but it's not available.");
            }

            // Loop dependency's dependencies. Add them BEFORE the main migration and previous dependencies.
            $dependencies = array_merge($this->getPendingDependencies($dependency), $dependencies);
        }

        return $dependencies;
    }

    /**
     * Returns the migration dependency list.
     *
     * @param class-string $migration The migration class
     *
     * @return class-string[] The dependency list
     */
    protected function getDependenciesProperty(string $migration): array
    {
        // Should be handled by interface, but since it a property, it's not... so should still be kept in case.
        // If the `dependencies` property exist, use it
        if (property_exists($migration, 'dependencies')) {
            return $migration::$dependencies;
        } else {
            return [];
        }
    }

    /**
     * Returns the migrations to be rollback.
     *
     * MigrationRollbackException will be thrown if something prevent the
     * migrations from being executed. If the array is returned, everything is
     * fine to proceed.
     *
     * @param int $steps Number of steps to rollback. Default to 1 (last ran migration)
     *
     * @return class-string[] An array of migration class to be rolled down.
     */
    public function getMigrationsForRollback(int $steps = 1): array
    {
        $migrations = $this->repository->list(steps: $steps, asc: false);

        $this->checkRollbackDependencies($migrations);

        return $migrations;
    }

    /**
     * Returns the migrations to be reset (all of them).
     *
     * MigrationRollbackException will be thrown if something prevent the
     * migrations from being executed. If the array is returned, everything is
     * fine to proceed.
     *
     * @return class-string[] An array of migration class to be rolled down.
     */
    public function getMigrationsForReset(): array
    {
        $migrations = $this->repository->list(asc: false);

        $this->checkRollbackDependencies($migrations);

        return $migrations;
    }

    /**
     * Return if the specified migration class can be rollback or not.
     *
     * @param class-string $migration
     *
     * @return bool True if can rollback, false if not.
     */
    public function canRollbackMigration(string $migration): bool
    {
        try {
            $this->validateRollbackMigration($migration);
        } catch (MigrationRollbackException $e) {
            return false;
        }

        return true;
    }

    /**
     * Test if a migration can be rollback.
     *
     * @param class-string   $migration
     * @param class-string[] $installed
     *
     * @throws MigrationRollbackException If something prevent migration to be rollback
     */
    public function validateRollbackMigration(string $migration, ?array $installed = null): void
    {
        // Can't rollback if migration is not installed
        if (!$this->repository->has($migration)) {
            throw new MigrationRollbackException('Migration is not installed : ' . $migration);
        }

        // Can't rollback anything if there's any stale migration
        if (count($stale = $this->getStale()) > 0) {
            throw new MigrationRollbackException('Stale migration detected : ' . implode(', ', $stale));
        }

        // If no installed, use the whole stack of installed
        if ($installed === null) {
            $installed = $this->getInstalled();
        }

        // We need to validate the dependencies of each installed migration
        // To make sure any of them doesn't depends on the one we wan to rollback.
        foreach ($installed as $installedMigration) {
            // Get all dependencies for $installed, make sure $migrations is not in them
            $dependencies = $this->getInstalledDependencies($installedMigration);
            if (in_array($migration, $dependencies, true)) {
                throw new MigrationRollbackException("$migration cannot be rolled back since $installedMigration depends on it.");
            }
        }
    }

    /**
     * We run across all step even if it's not required, as this is run
     * recursively across all dependencies.
     *
     * @param class-string $migrationClass
     *
     * @return class-string[] This migration and it's dependencies.
     */
    protected function getInstalledDependencies(string $migrationClass): array
    {
        // Return variable
        $dependencies = [];

        // Loop dependencies and validate them
        foreach ($this->getDependenciesProperty($migrationClass) as $dependency) {
            // Make sure dependency exist. Otherwise it's a dead end.
            if (!$this->locator->has($dependency)) {
                throw new MigrationRollbackException("$migrationClass depends on $dependency, but it's not available.");
            }

            // Loop dependency's dependencies. Add them BEFORE the main migration and previous dependencies.
            $dependencies = array_merge($this->getPendingDependencies($dependency), $dependencies);
        }

        return $dependencies;
    }

    /**
     * Loop all migrations and test each one for rollback, recursively passing
     * the remaining migrations, simulating the removal of each migration as
     * each loop is parsed.
     *
     * N.B.: The `installed` migration stack are NOT passed to `testRollbackMigration`
     * here, only the array of migrations passed as argument. By design, all
     * migrations received, in this class, will be in reverse order they have been
     * run. It works because it's theoretically impossible for an older batch to
     * depend on a newer batch. The only way would be for a definition to have
     * changed. To avoid this, both install should cloned, then remove from this,
     * but the current solution is more optimized.
     *
     * @param class-string[] $migrations
     */
    protected function checkRollbackDependencies(array $migrations): void
    {
        foreach ($migrations as $migration) {
            // Exception will be thrown if can't rollback.
            $this->validateRollbackMigration($migration, $migrations);

            // Remove the migration form the list, to simulate it's been
            // rollback for the next pass.
            $migrations = array_filter($migrations, fn ($m) => $m !== $migration);
        }
    }

    /**
     * Run all the specified migrations up. Check that dependencies are met before running.
     *
     * @param bool $step Migrations will be run in individual step so they can be rolled back individually (default = false)
     *
     * @throws MigrationDependencyNotMetException if a dependencies is not met among pending migration.
     *
     * @return class-string[] The list of ran migrations class
     */
    public function migrate(bool $step = false): array
    {
        // Get pending migrations.
        // MigrationDependencyNotMetException will be thrown here if a dependency is not met.
        $pending = $this->getPending();

        // Don't go further if no pending migration.
        if (count($pending) === 0) {
            return [];
        }

        // Get the next batch number.
        $batch = $this->repository->getNextBatchNumber();

        // Spin through ordered pending migrations, apply the changes to the
        // databases and log them to the repository.
        foreach ($pending as $migrationClass) {
            // Get the migration instance
            $migration = $this->locator->get($migrationClass);

            // Place migration in callable for transaction
            $callback = function () use ($migration) {
                $migration->up();
            };

            // TODO : See if Transaction still needs to be used
            if ($this->transactionsSupported()) {
                $this->getConnection()->transaction($callback);
            } else {
                $callback();
            }

            // Log migration to the repository.
            $this->repository->log($migrationClass, $batch);

            if ($step) {
                $batch++;
            }
        }

        return $pending;
    }

    /**
     * Pretend to migrate, return would be queries for debugging purpose.
     *
     * @throws MigrationDependencyNotMetException if a dependencies is not met among pending migration.
     *
     * @return array<class-string, mixed[]> The list of queries, grouped by migration.
     */
    public function pretendToMigrate(): array
    {
        // Get pending migrations.
        // MigrationDependencyNotMetException will be thrown here if a dependency is not met.
        $pending = $this->getPending();

        // Don't go further if no pending migration.
        if (count($pending) === 0) {
            return [];
        }

        // Prepare return log
        $log = [];

        // Spin through ordered pending migrations, apply the changes to the
        // databases and log them to the repository.
        foreach ($pending as $migrationClass) {
            $migration = $this->locator->get($migrationClass);

            // Get the connection instance
            $connection = $this->getConnection();

            // Get the queries
            $queries = $connection->pretend(function () use ($migration) {
                $migration->up();
            });

            // Add queries to log
            $log[$migrationClass] = $queries;
        }

        return $log;
    }

    /**
     * Rollback the last migration operation.
     *
     * @param int $steps Number of steps to rollback. Default to 1 (last ran migration batch)
     *
     * @throws MigrationRollbackException If something prevent migration to be rollback
     *
     * @return class-string[] The list of rolledback migration classes
     */
    public function rollback(int $steps = 1): array
    {
        // Get migrations to rollback.
        // MigrationDependencyNotMetException will be thrown here if a dependency is not met.
        $migrations = $this->getMigrationsForRollback($steps);

        // Don't go further if nothing to rollback.
        if (count($migrations) === 0) {
            return [];
        }

        // Apply change to database
        $this->runDownMigrations($migrations);

        return $migrations;
    }

    /**
     * Rollback a specific migration.
     *
     * @param class-string $migrationClassName The Migration to rollback
     * @param array        $options
     *
     * @return array The list of rolledback migration classes
     */
    // TODO : Change $option for specific arguments
    /*public function rollbackMigration($migrationClassName, array $options = [])
    {
        $this->notes = [];

        // Get the migration detail from the repository
        $migration = $this->repository->get($migrationClassName);

        // Make sure the migration was found. If not, return same empty array
        // as the main rollback method
        // TODO : Exception should be thrown by repo
        if (!$migration) {
            return [];
        }

        // Rollback the migration
        // TODO : `rollbackMigrations` can stay apart, as reset and single rollback also use it
        // TODO : Use Analyser here, then loop them and "runDown"
        return $this->rollbackMigrations([$migration['migration']], $options);

        // Apply change to database
        $this->runDownMigrations([$migrations]);
    }*/

    /**
     * Pretend to rollback, return would be queries for debugging purpose.
     *
     * @param int $steps Number of steps to rollback. Default to 1 (last ran migration batch)
     *
     * @throws MigrationRollbackException If something prevent migration to be rollback
     *
     * @return array<class-string, string[]> The list of queries, grouped by migration.
     */
    public function pretendToRollback(int $steps = 1): array
    {
        // Get migrations to rollback.
        // MigrationDependencyNotMetException will be thrown here if a dependency is not met.
        $migrations = $this->getMigrationsForRollback($steps);

        // Don't go further if nothing to rollback.
        if (count($migrations) === 0) {
            return [];
        }

        // Pretend to run down migrations.
        $log = $this->pretendToRunDownMigrations($migrations);

        return $log;
    }

    /**
     * Rolls all of the currently applied migrations back.
     *
     * @throws MigrationRollbackException If something prevent migration to be rollback
     *
     * @return class-string[] An array of all the rolledback migration classes
     */
    public function reset(): array
    {
        // Get migrations to reset.
        // MigrationDependencyNotMetException will be thrown here if a dependency is not met.
        $migrations = $this->getMigrationsForReset();

        // Don't go further if nothing to rollback.
        if (count($migrations) === 0) {
            return [];
        }

        // Apply change to database
        $this->runDownMigrations($migrations);

        return $migrations;
    }

    /**
     * Pretend to roll all of the currently applied migrations back.
     *
     * @throws MigrationRollbackException If something prevent migration to be rollback
     *
     * @return array<class-string, string[]> The list of queries, grouped by migration.
     */
    public function pretendToReset(): array
    {
        // Get migrations to reset.
        // MigrationDependencyNotMetException will be thrown here if a dependency is not met.
        $migrations = $this->getMigrationsForReset();

        // Don't go further if nothing to rollback.
        if (count($migrations) === 0) {
            return [];
        }

        // Pretend to run down migrations.
        $log = $this->pretendToRunDownMigrations($migrations);

        return $log;
    }

    /**
     * Spin through ordered pending migrations, apply the changes to the
     * databases and log them to the repository.
     *
     * @param class-string[] $migrations The migrations classes to run down
     */
    protected function runDownMigrations(array $migrations): void
    {
        foreach ($migrations as $migrationClass) {
            // Get the migration instance
            $migration = $this->locator->get($migrationClass);

            // Place migration in callable for transaction
            $callback = function () use ($migration) {
                $migration->down();
            };

            // TODO : See if Transaction still needs to be used
            if ($this->transactionsSupported()) {
                $this->getConnection()->transaction($callback);
            } else {
                $callback();
            }

            // Log migration removal to the repository.
            $this->repository->remove($migrationClass);
        }
    }

    /**
     * Spin through ordered pending migrations, pretend to run them down and
     * return the queries log.
     *
     * @param class-string[] $migrations The migrations classes to run down
     *
     * @return array<class-string, string[]> The list of queries, grouped by migration.
     */
    protected function pretendToRunDownMigrations(array $migrations): array
    {
        // Prepare return log
        $log = [];

        foreach ($migrations as $migrationClass) {
            // Get the migration instance
            $migration = $this->locator->get($migrationClass);

            // Get the connection instance
            $connection = $this->getConnection();

            // Get the queries
            $queries = $connection->pretend(function () use ($migration) {
                $migration->down();
            });

            // Add queries to log
            $log[$migrationClass] = $queries;
        }

        return $log;
    }

    /**
     * Get the migration repository instance.
     *
     * @return MigrationRepositoryInterface
     */
    public function getRepository(): MigrationRepositoryInterface
    {
        return $this->repository;
    }

    /**
     * Set the migration repository instance.
     *
     * @param MigrationRepositoryInterface $repository
     */
    public function setRepository(MigrationRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * Determine if the migration repository exists.
     *
     * @return bool If the repository exist
     */
    public function repositoryExists(): bool
    {
        return $this->repository->exists();
    }

    /**
     * Get the migration locator instance.
     *
     * @return MigrationLocatorInterface
     */
    public function getLocator(): MigrationLocatorInterface
    {
        return $this->locator;
    }

    /**
     * Set the migration locator instance.
     *
     * @param MigrationLocatorInterface $locator
     */
    public function setLocator(MigrationLocatorInterface $locator): static
    {
        $this->locator = $locator;

        return $this;
    }

    /**
     * Return the database connection name.
     *
     * @return string|null The connection name (default: null, aka the default connection)
     */
    public function getConnectionName(): ?string
    {
        return $this->db->getDatabaseManager()->getDefaultConnection();
    }

    /**
     * Return the database connection instance.
     *
     * @return Connection
     */
    protected function getConnection(): Connection
    {
        return $this->db->getConnection($this->getConnectionName());
    }

    /**
     * Return if database transaction are supported by current db connection.
     *
     * @return bool
     */
    protected function transactionsSupported(): bool
    {
        return $this->getConnection()
                    ->getSchemaGrammar()
                    ->supportsSchemaTransactions();
    }
}
