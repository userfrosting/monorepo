<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Database\Migrations;

use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Database\Migrator\Migrator;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;

/**
 * ActivitiesTable Migration Test.
 */
class MigrationsTest extends CoreTestCase
{
    public function testMigrations(): void
    {
        /** @var Builder */
        $builder = $this->getService(Builder::class);

        /** @var Migrator */
        $migrator = $this->getService(Migrator::class);

        // Initiate migrations
        $migrator->reset();
        $migrator->migrate();

        // Assert state for each tables
        foreach ($this->tablesProvider() as $table => $expectation) {
            $result = $builder->getColumnListing($table);
            sort($expectation);
            sort($result);
            $this->assertSame($expectation, $result);
        }

        // Reset database
        $migrator->rollback();

        // Redo assertions for each (now empty) table
        foreach ($this->tablesProvider() as $table => $columns) {
            $this->assertSame([], $builder->getColumnListing($table));
        }
    }

    /** @return array<string, string[]> */
    public function tablesProvider(): array
    {
        return [
            'sessions' => [
                'id',
                'user_id',
                'ip_address',
                'user_agent',
                'payload',
                'last_activity',
            ],
            'throttles' => [
                'id',
                'type',
                'ip',
                'request_data',
                'created_at',
                'updated_at',
            ],
        ];
    }
}
