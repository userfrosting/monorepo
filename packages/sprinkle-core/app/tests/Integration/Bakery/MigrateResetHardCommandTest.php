<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\Bakery;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Core\Bakery\MigrateResetHardCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Testing\BakeryTester;

/**
 * MigrateResetCommand tests
 */
class MigrateResetHardCommandTest extends CoreTestCase
{
    public function tearDown(): void
    {
        // Drop table, in case test fails and table stays up.
        $schema = $this->getService(Builder::class);
        if ($schema->hasTable('migrate_reset_hard_command_test')) {
            $schema->drop('migrate_reset_hard_command_test');
        }

        parent::tearDown();
    }

    public function testPretendHardReset(): void
    {
        // Get and run command
        $command = $this->getService(MigrateResetHardCommand::class);

        // Create a table
        $schema = $this->getService(Builder::class);
        $this->assertFalse($schema->hasTable('migrate_reset_hard_command_test'));
        $schema->create('migrate_reset_hard_command_test', function (Blueprint $table) {
            $table->id();
            $table->string('foo');
        });
        $this->assertTrue($schema->hasTable('migrate_reset_hard_command_test'));

        // Run command again
        $result = BakeryTester::runCommand($command, input: ['--pretend' => true]);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $display = $result->getDisplay();
        $this->assertStringContainsString('Running migrate:reset:hard in pretend mode', $display);
        $this->assertStringContainsString('Dropping table `migrate_reset_hard_command_test`...', $display);
        $this->assertTrue($schema->hasTable('migrate_reset_hard_command_test'));

        // Actually drop the table now
        $result = BakeryTester::runCommand($command, userInput: ['y']);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $display = $result->getDisplay();
        $this->assertStringContainsString('Dropping table `migrate_reset_hard_command_test`...', $display);
        $this->assertStringContainsString('Do you really wish to continue ?', $display);
        $this->assertStringContainsString('Hard reset successful', $display);
        $this->assertFalse($schema->hasTable('migrate_reset_hard_command_test'));
        $result = BakeryTester::runCommand($command, input: ['--pretend' => true]);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $display = $result->getDisplay();
        $this->assertStringContainsString('Running migrate:reset:hard in pretend mode', $display);
        $this->assertStringContainsString('No tables found', $display);
    }
}
