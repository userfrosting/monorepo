<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Arr;
use UserFrosting\Sprinkle\Core\Database\MigrationInterface;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationDependencyNotMetException;
use UserFrosting\Sprinkle\Core\Exceptions\MigrationRollbackException;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;

/**
 * Migrator utility used to manage and run database migrations
 */
class Migrator
{
    /**
     * @var string The connection name
     */
    protected $connection;

    /**
     * @var array The notes for the current operation.
     */
    protected $notes = [];

    /**
     * Constructor.
     *
     * @param Capsule                      $db         The database instance
     * @param MigrationRepositoryInterface $repository The migration repository
     * @param MigrationLocatorInterface    $locator    The Migration locator
     */
    // TODO : Might be more helpful to send connection directly here
    public function __construct(
        // protected Capsule $db,
        protected MigrationRepositoryInterface $repository,
        protected MigrationLocatorInterface $locator,
    ) {
    }

    /**
     * Get installed migrations
     *
     * @return string[] An array of migration class names in the order they where ran.
     */
    public function getInstalled(): array
    {
        return $this->repository->list();
    }

    /**
     * Get migrations available and registered to be run.
     *
     * @return string[] An array of migration class names.
     */
    public function getAvailable(): array
    {
        return $this->locator->list();
    }

    /**
     * Returns migration that are installed, but not available.
     *
     * @return string[] An array of migration class names.
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
     * @return string[] An array of migration class names.
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
     * @param string $migrationClass
     *
     * @return string[] This migration and it's dependencies.
     */
    protected function getPendingDependencies(string $migrationClass): array
    {
        // Get migration instance
        $migration = $this->locator->get($migrationClass);

        // Start with the migration in the return.
        // This allows the main migration to be placed in the correct order
        // When doing a nested call.
        $dependencies = [$migrationClass];

        // Loop dependencies and validate them
        foreach ($this->getDependenciesProperty($migration) as $dependency) {

            // The dependency might already be installed. Check that first.
            // Skip if it's the case (we don't want it pending again).
            // This allows to accept stale (installed, but not available
            // migration) as valid dependencies.
            if (in_array($dependency, $this->getInstalled())) {
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
     * @param MigrationInterface $migration The migration instance
     *
     * @return string[] The dependency list
     */
    protected function getDependenciesProperty(MigrationInterface $migration): array
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
     * @return string[] An array of migration class to be rolled down.
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
     * @return string[] An array of migration class to be rolled down.
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
     * @param string $migration
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
     * @param  string                     $migration
     * @param  string[]                   $installed
     * @throws MigrationRollbackException If something prevent migration to be rollback
     */
    public function validateRollbackMigration(string $migration, ?array $installed = null): void
    {
        // Can't rollback if migration is not installed
        if (!$this->repository->has($migration)) {
            throw new MigrationRollbackException('Migration is not installed : ' . $migration);
        }

        // Can't rollback anything if there's any stale migration
        if (!empty($stale = $this->getStale())) {
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
            if (in_array($migration, $dependencies)) {
                throw new MigrationRollbackException("$migration cannot be rolled back since $installedMigration depends on it.");
            }
        }
    }

    /**
     * We run across all step even if it's not required, as this is run
     * recursively across all dependencies.
     *
     * @param string $migrationClass
     *
     * @return string[] This migration and it's dependencies.
     */
    protected function getInstalledDependencies(string $migrationClass): array
    {
        // Get migration instance
        $migration = $this->locator->get($migrationClass);

        // Return variable
        $dependencies = [];

        // Loop dependencies and validate them
        foreach ($this->getDependenciesProperty($migration) as $dependency) {

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
     * @param string[] $migrations
     */
    protected function checkRollbackDependencies(array $migrations): void
    {
        foreach ($migrations as $migration) {

            // Exception will be thrown if can't rollback.
            $this->validateRollbackMigration($migration, $migrations);

            // Remove the migration form the list, to simulate it's been
            // rollback for the next pass.
            $migrations = array_filter($migrations, fn ($m) => $m != $migration);
        }
    }







    /**
     * Run all the specified migrations up. Check that dependencies are met before running.
     *
     * @param array $options Options for the current operations [step, pretend]
     *
     * @return array The list of ran migrations
     */
    public function run(array $options = [])
    {
        $this->notes = [];

        // Get outstanding migrations classes that requires to be run up
        $pendingMigrations = $this->getPendingMigrations();

        // First we will just make sure that there are any migrations to run. If there
        // aren't, we will just make a note of it to the developer so they're aware
        // that all of the migrations have been run against this database system.
        if (count($pendingMigrations) == 0) {
            return [];
        }

        // Next we need to validate that all pending migration dependencies are met or WILL BE MET
        // This operation is important as it's the one that place the outstanding migrations
        // in the correct order, making sure a migration script won't fail because the table
        // it depends on has not been created yet (for example).
        // TODO : Inject analysers
        $analyser = new Analyser($pendingMigrations, $this->getRanMigrations());

        // Any migration without a fulfilled dependency will cause this script to throw an exception
        // TODO : Exception is now thrown by Analyser
        if ($unfulfillable = $analyser->getUnfulfillable()) {
            $messages = ['Unfulfillable migrations found :: '];
            foreach ($unfulfillable as $migration => $dependency) {
                $messages[] = "=> $migration (Missing dependency : $dependency)";
            }

            throw new \Exception(implode("\n", $messages));
        }

        // Run pending migration up
        // TODO : No need for two methods, merge pending here
        return $this->runPending($analyser->getFulfillable(), $options);
    }

    /**
     * Get the migration classes that have not yet run.
     *
     * @param array $available The available migrations returned by the migration locator
     * @param array $ran       The list of already ran migrations returned by the migration repository
     *
     * @return array The list of pending migrations, ie the available migrations not ran yet
     */
    // TODO : Should be replaced by method in analyser
    // protected function pendingMigrations(array $available, array $ran)
    // {
    //     return collect($available)->reject(function ($migration) use ($ran) {
    //         return collect($ran)->contains($migration);
    //     })->values()->all();
    // }

    /**
     * Run an array of migrations.
     *
     * @param array $migrations An array of migrations classes names to be run (unsorted, unvalidated)
     * @param array $options    The options for the current operation [step, pretend]
     */
    protected function runPending(array $migrations, array $options = [])
    {
        // Next, we will get the next batch number for the migrations so we can insert
        // correct batch number in the database migrations repository when we store
        // each migration's execution.
        $batch = $this->repository->getNextBatchNumber();

        // We extract a few of the options.
        // TODO : Pretend and Step need to be argument of this method, instead of $options
        $pretend = Arr::get($options, 'pretend', false);
        $step = Arr::get($options, 'step', 0);

        // We now have an ordered array of migrations, we will spin through them and run the
        // migrations "up" so the changes are made to the databases. We'll then log
        // that the migration was run so we don't repeat it next time we execute.
        foreach ($migrations as $migrationClassName) {
            $this->runUp($migrationClassName, $batch, $pretend);

            if ($step) {
                $batch++;
            }
        }

        return $migrations;
    }

    /**
     * Run "up" a migration class.
     *
     * @param string $migrationClassName The migration class name
     * @param int    $batch              The current batch number
     * @param bool   $pretend            If this operation should be pretended / faked
     */
    protected function runUp($migrationClassName, $batch, $pretend)
    {
        // First we will resolve a "real" instance of the migration class from
        // the class name. Once we have the instances we can run the actual
        // command such as "up" or "down", or we can just simulate the action.
        $migration = $this->resolve($migrationClassName);

        // Move into pretend mode if requested
        if ($pretend) {
            return $this->pretendToRun($migration, 'up');
        }

        // Run the actual migration
        // TODO : Move code from "runMigration" here, to remove string argument
        $this->runMigration($migration, 'up');

        // Once we have run a migrations class, we will log that it was run in this
        // repository so that we don't try to run it next time we do a migration
        // in the application. A migration repository keeps the migrate order.
        $this->repository->log($migrationClassName, $batch);

        // TODO : Remove. Duplicate the return of "run"
        $this->note("<info>Migrated:</info>  {$migrationClassName}");
    }

    /**
     * Rollback the last migration operation.
     *
     * @param array $options The options for the current operation [steps, pretend]
     *
     * @return array The list of rolledback migration classes
     */
    // TODO : Change option
    public function rollback(array $options = [])
    {
        $this->notes = [];

        // We want to pull in the last batch of migrations that ran on the previous
        // migration operation. We'll then reverse those migrations and run each
        // of them "down" to reverse the last migration "operation" which ran.
        $migrations = $this->getMigrationsForRollback($options);

        if (count($migrations) === 0) {
            return [];
        } 

        // TODO : `rollbackMigrations` can stay apart, as reset and single rollback also use it
        // TODO : Use Analyser here, then loop them and "runDown"
        return $this->rollbackMigrations($migrations, $options);
    }

    /**
     * Rollback a specific migration.
     *
     * @param string $migrationClassName The Migration to rollback
     * @param array  $options
     *
     * @return array The list of rolledback migration classes
     */
    // TODO : Change $option for specific arguments
    public function rollbackMigration($migrationClassName, array $options = [])
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
        return $this->rollbackMigrations([$migration->migration], $options);
    }

    /**
     * Get the migrations for a rollback operation.
     *
     * @param array $options The options for the current operation
     *
     * @return array An ordered array of migrations to rollback
     */
    // TODO : Move back into "rollback", step as argument with default value|null
    /*protected function getMigrationsForRollback(array $options)
    {
        $steps = Arr::get($options, 'steps', 0);
        if ($steps > 0) {
            return $this->repository->list($steps, 'desc');
        } else {
            return $this->repository->last();
        }
    }*/

    /**
     * Rollback the given migrations.
     *
     * @param array $migrations An array of migrations to rollback formatted as an eloquent collection
     * @param array $options    The options for the current operation
     *
     * @return array The list of rolledback migration classes
     */
    // TODO : This is done by analyser now, except "runDown"
    protected function rollbackMigrations(array $migrations, array $options)
    {
        $rolledBack = [];

        // Get the available migration classes in the filesystem
        $availableMigrations = collect($this->getAvailableMigrations());

        // Extract some options
        $pretend = Arr::get($options, 'pretend', false);

        // Check for dependencies
        $this->checkRollbackDependencies($migrations);

        // Next we will run through all of the migrations and call the "down" method
        // which will reverse each migration in order. This getLast method on the
        // repository already returns these migration's class names in reverse order.
        foreach ($migrations as $migration) {

            // We have to make sure the class exist first
            if (!$availableMigrations->contains($migration)) {
                // NOTE This next was commented because if a class doesn't exist,
                // you'll get stuck and prevent further classes to be rolledback
                // until this class is put back in the system. Might want to
                // display a warning instead of silently skipping it. See related "todo" in "reset" method
                //throw new \Exception("Can't rollback migrations `$migration`. The migration class doesn't exist");
                $this->note("<info>WARNING:</info> Can't rollback migrations `$migration`. The migration class doesn't exist");
                continue;
            }

            // Add the migration to the list of rolledback migration
            $rolledBack[] = $migration;

            // Run the migration down
            // TODO : This need to be kept
            $this->runDown($migration, $pretend);
        }

        return $rolledBack;
    }

    /**
     * Check if migrations can be rolledback.
     *
     * @param array $migrations The migrations classes to rollback
     *
     * @throws \Exception If rollback can't be performed
     */
    // TODO : This is done by analyser now
    // protected function checkRollbackDependencies(array $migrations)
    // {
    //     // Get ran migrations
    //     $ranMigrations = $this->getRanMigrations();

    //     // Setup rollback analyser
    //     $analyser = new RollbackAnalyser($ranMigrations, $migrations);

    //     // Any rollback that creates an unfulfilled dependency will cause this script to throw an exception
    //     if ($unfulfillable = $analyser->getUnfulfillable()) {
    //         $messages = ["Some migrations can't be rolled back since the other migrations depends on it :: "];
    //         foreach ($unfulfillable as $migration => $dependency) {
    //             $messages[] = "=> $dependency is a dependency of $migration";
    //         }

    //         throw new \Exception(implode("\n", $messages));
    //     }
    // }

    /**
     * Rolls all of the currently applied migrations back.
     *
     * @param bool $pretend Should this operation be pretended
     *
     * @return array An array of all the rolledback migration classes
     */
    public function reset($pretend = false)
    {
        $this->notes = [];

        // We get the list of all the migrations class available and reverse
        // said list so we can run them back in the correct order for resetting
        // this database. This will allow us to get the database back into its
        // "empty" state and ready to be migrated "up" again.
        //
        // !TODO :: Should compare to the install list to make sure no outstanding migration (ran, but with no migration class anymore) still exist in the db
        $migrations = array_reverse($this->getRanMigrations()); // GetInstalled

        // TODO : This could go in "rollback Migration"
        if (count($migrations) === 0) {
            return [];
        }

        // TODO : `rollbackMigrations` can stay apart, as reset and single rollback also use it
        // TODO : Use Analyser in rollbackMigrations, then loop them and "runDown"
        return $this->rollbackMigrations($migrations, compact('pretend'));
    }

    /**
     * Run "down" a migration instance.
     *
     * @param string $migrationClassName The migration class name
     * @param bool   $pretend            Is the operation should be pretended
     */
    // TODO : Most of this could go back into "rollbackMigrations" directly
    protected function runDown($migrationClassName, $pretend)
    {
        // We resolve an instance of the migration. Once we get an instance we can either run a
        // pretend execution of the migration or we can run the real migration.
        $instance = $this->resolve($migrationClassName);

        if ($pretend) {
            return $this->pretendToRun($instance, 'down');
        }

        $this->runMigration($instance, 'down');

        // Once we have successfully run the migration "down" we will remove it from
        // the migration repository so it will be considered to have not been run
        // by the application then will be able to fire by any later operation.
        $this->repository->remove($migrationClassName);

        $this->note("<info>Rolled back:</info>  {$migrationClassName}");
    }

    /**
     * Run a migration inside a transaction if the database supports it.
     * Note : As of Laravel 5.4, only PostgresGrammar supports it.
     *
     * @param MigrationInterface $migration The migration instance
     * @param string             $method    The method used [up, down]
     */
    // TODO : REmove this and Use up and down directly to avoid using the string arg
    // TODO : See if Transaction still needs to be used
    protected function runMigration(MigrationInterface $migration, $method)
    {
        $callback = function () use ($migration, $method) {
            $migration->{$method}();
        };

        if ($this->getSchemaGrammar()->supportsSchemaTransactions()) {
            $this->getConnection()->transaction($callback);
        } else {
            $callback();
        }
    }

    /**
     * Pretend to run the migrations.
     *
     * @param MigrationInterface $migration The migration instance
     * @param string             $method    The method used [up, down]
     */
    // TODO : Output will need to be managed
    protected function pretendToRun(MigrationInterface $migration, $method)
    {
        $name = get_class($migration);
        $this->note("\n<info>$name</info>");

        foreach ($this->getQueries($migration, $method) as $query) {
            $this->note("> {$query['query']}");
        }
    }

    /**
     * Get all of the queries that would be run for a migration.
     *
     * @param MigrationInterface $migration The migration instance
     * @param string             $method    The method used [up, down]
     *
     * @return array The queries executed by the processed schema
     */
    protected function getQueries(MigrationInterface $migration, $method)
    {
        // Get the connection instance
        $connection = $this->getConnection();

        return $connection->pretend(function () use ($migration, $method) {
            $migration->{$method}();
        });
    }

    /**
     * Resolve a migration instance from it's class name.
     *
     * @param string $migrationClassName The class name
     *
     * @return MigrationInterface The migration class instance
     */
    // Don't need anymore, as DI will handle this in Locator
    // public function resolve($migrationClassName)
    // {
    //     if (!class_exists($migrationClassName)) {
    //         throw new BadClassNameException("Unable to find the migration class '$migrationClassName'. Run 'php bakery migrate:clean' to remove stale migrations.");
    //     }

    //     $migration = new $migrationClassName($this->getSchemaBuilder());

    //     if (!$migration instanceof MigrationInterface) {
    //         throw new \Exception('Migration must be an instance of `' . MigrationInterface::class . '`');
    //     }

    //     return $migration;
    // }

    /**
     * Get all of the migration files in a given path.
     *
     * @return array The list of migration classes found in the filesystem
     */
    // TODO 
    public function getAvailableMigrations()
    {
        return $this->locator->all();
    }

    /**
     * Get a list of all ran migrations.
     *
     * @param int    $steps Number of batch to return
     * @param string $order asc|desc
     *
     * @return array
     */
    // TODO 
    public function getRanMigrations($steps = -1, $order = 'asc')
    {
        return $this->repository->list($steps, $order);
    }

    /**
     * Get a list of pending migrations.
     *
     * @return array
     */
    // TODO : Use Analyser directly
    public function getPendingMigrations()
    {
        $available = $this->getAvailableMigrations();
        $ran = $this->getRanMigrations();

        return $this->pendingMigrations($available, $ran);
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
    public function setLocator(MigrationLocatorInterface $locator): void
    {
        $this->locator = $locator;
    }

    /**
     * Get the schema builder.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    // Don't need anymore in resolve
    // public function getSchemaBuilder()
    // {
    //     return $this->getConnection()->getSchemaBuilder();
    // }

    /**
     * Return the connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    public function getConnection()
    {
        return $this->db->getConnection($this->connection);
    }

    /**
     * Define which connection to use.
     *
     * @param string $name The connection name
     */
    public function setConnection($name)
    {
        $this->repository->setConnectionName($name);
        $this->connection = $name;
    }

    /**
     * Get instance of Grammar.
     *
     * @return \Illuminate\Database\Schema\Grammars\Grammar
     */
    protected function getSchemaGrammar()
    {
        return $this->getConnection()->getSchemaGrammar();
    }

    /**
     * Raise a note event for the migrator.
     *
     * @param string $message The message
     */
    protected function note($message)
    {
        $this->notes[] = $message;
    }

    /**
     * Get the notes for the last operation.
     *
     * @return array An array of notes
     */
    public function getNotes()
    {
        return $this->notes;
    }
}
