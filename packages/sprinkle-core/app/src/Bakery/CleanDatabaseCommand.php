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

use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Sprinkle\Core\Bakery\Event\DatabasePruneEvent;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\PrunableModelInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Session;
use UserFrosting\Sprinkle\Core\Database\Models\Throttle;

/**
 * Prune expired records from database models.
 */
class CleanDatabaseCommand extends Command
{
    use WithSymfonyStyle;

    public function __construct(protected EventDispatcherInterface $eventDispatcher)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('clean:database')
             ->setDescription('Prunes expired records from database tables');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('Pruning expired database records');

        $event = new DatabasePruneEvent([
            Session::class,
            Throttle::class,
        ]);
        $this->eventDispatcher->dispatch($event);

        // Start progress bar for each model to be pruned
        $this->io->progressStart(count($event->getModels()));
        $prunedRecords = [];

        foreach ($event->getModels() as $modelClass) {
            /** @var PrunableModelInterface $model */
            $model = new $modelClass();
            $pruned = $model->pruneAll();
            $modelName = basename(str_replace('\\', '/', $modelClass));
            $prunedRecords[] = sprintf('<info>Pruned %d records from %s</info>', $pruned, $modelName);
            $this->io->progressAdvance();
        }

        // Display pruned records
        $this->io->progressFinish();
        $this->io->listing($prunedRecords);
        $this->io->success('Database cleanup complete');

        return self::SUCCESS;
    }
}
