<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
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
    public function testPretendHardReset(): void
    {
        // Get and run command
        $command = $this->ci->get(MigrateResetHardCommand::class);

        // Create a table
        $schema = $this->ci->get(Builder::class);
        $this->assertFalse($schema->hasTable('MigrateResetHardCommandTest'));
        $schema->create('MigrateResetHardCommandTest', function (Blueprint $table) {
            $table->id();
            $table->string('foo');
        });
        $this->assertTrue($schema->hasTable('MigrateResetHardCommandTest'));

        // Run command again
        $result = BakeryTester::runCommand($command, input: ['--pretend' => true]);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $display = $result->getDisplay();
        $this->assertStringContainsString('Running migrate:reset:hard in pretend mode', $display);
        $this->assertStringContainsString('Dropping table `MigrateResetHardCommandTest`...', $display);
        $this->assertStringContainsString('drop table "MigrateResetHardCommandTest"', $display);
        $this->assertTrue($schema->hasTable('MigrateResetHardCommandTest'));

        // Actually drop the table now
        $result = BakeryTester::runCommand($command, userInput: ['y']);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $display = $result->getDisplay();
        $this->assertStringContainsString('Dropping table `MigrateResetHardCommandTest`...', $display);
        $this->assertStringContainsString('Do you really wish to continue ?', $display);
        $this->assertStringContainsString('Hard reset successful', $display);
        $this->assertFalse($schema->hasTable('MigrateResetHardCommandTest'));
        $result = BakeryTester::runCommand($command, input: ['--pretend' => true]);

        // Assert some output
        $this->assertSame(0, $result->getStatusCode());
        $display = $result->getDisplay();
        $this->assertStringContainsString('Running migrate:reset:hard in pretend mode', $display);
        $this->assertStringContainsString('No tables found', $display);
    }
}
