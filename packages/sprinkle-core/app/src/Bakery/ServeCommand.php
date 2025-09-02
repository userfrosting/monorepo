<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Bakery\Helper\ShellCommandHelper;

/**
 * Alias for `php serve` command.
 */
final class ServeCommand extends Command
{
    use WithSymfonyStyle;
    use ShellCommandHelper;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $help = [
            'Run the php built-in web server to test your application.',
            'This is a simple way to test your application without having to configure a full web server.',
            'Hit `<info>ctrl+c</info>` to quit.',
        ];

        $this->setName('serve')
             ->addOption('port', 'p', InputOption::VALUE_REQUIRED, 'The port to serve the application on', '8080')
             ->setDescription('Alias for `php -S` command')
             ->setHelp(implode(' ', $help));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('PHP built-in web server');
        $this->io->info('Press `ctrl+c` to quit');

        $port = $input->getOption('port');
        $this->executeCommand("php -S localhost:$port -t public");

        return self::SUCCESS;
    }
}
