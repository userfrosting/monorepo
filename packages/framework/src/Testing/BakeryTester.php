<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Testing;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Trait that provide `runCommand` method to test Bakery commands.
 * Requires the CI (\UserFrosting\Testing\TestCase).
 */
class BakeryTester
{
    /**
     * Execute Bakery command.
     *
     * @param Command $command   Commands to test.
     * @param mixed[] $input     An array of command arguments and options
     * @param mixed[] $userInput An Array of strings representing each input passed to the command input stream
     *
     * @return CommandTester
     */
    public static function runCommand(
        Command $command,
        array $input = [],
        array $userInput = [],
        int $verbosity = OutputInterface::VERBOSITY_NORMAL,
    ): CommandTester {
        // Create app and add command to it
        $app = new Application();
        $app->add($command);

        // Add the command to the input to create the execute argument
        $executeDefinition = array_merge([
            'command' => $command->getName(),
        ], $input);

        // Create command tester
        $commandTester = new CommandTester($command);

        // Set user input
        if (count($userInput) !== 0) {
            $commandTester->setInputs($userInput);
        }

        // Execute command
        $commandTester->execute($executeDefinition, ['verbosity' => $verbosity]);

        return $commandTester;
    }
}
