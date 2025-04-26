<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Monolog\Handler\TestHandler;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Log\QueryLogger;
use UserFrosting\Sprinkle\Core\Log\QueryLoggerInterface;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase as TestCase;

/**
 * Test service implementation works.
 * This test is agnostic of the actual config. It only test all the parts works together in a default env.
 */
class DatabaseServiceTest extends TestCase
{
    public function testService(): void
    {
        $this->assertInstanceOf(Capsule::class, $this->ci->get(Capsule::class)); // @phpstan-ignore-line
        $this->assertInstanceOf(Connection::class, $this->ci->get(Connection::class)); // @phpstan-ignore-line
        $this->assertInstanceOf(Builder::class, $this->ci->get(Builder::class)); // @phpstan-ignore-line
    }

    public function testWithQueryLogger(): void
    {
        // Enable debug queries on config service
        $config = $this->ci->get(Config::class);
        $config->set('debug.queries', true);
        $this->ci->set(Config::class, $config);

        // Set the QueryLogger with test handler
        $handler = new TestHandler();
        $logger = new QueryLogger($handler);
        $this->ci->set(QueryLoggerInterface::class, $logger);

        // Get capsule service
        $capsule = $this->ci->get(Capsule::class);

        // Run a test query
        $capsule->getConnection()->select('SELECT 1');

        // Get the query logger records
        $records = $handler->getRecords();
        $this->assertNotSame([], $records);
        $this->assertCount(1, $records);
        $this->assertContainsOnlyInstancesOf(\Monolog\LogRecord::class, $records);
        $this->assertStringContainsString('Query executed on database', $records[0]['message']); // @phpstan-ignore-line
    }
}
