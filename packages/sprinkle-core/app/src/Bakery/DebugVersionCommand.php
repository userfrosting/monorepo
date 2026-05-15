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
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;
use UserFrosting\Sprinkle\Core\Validators\NodeVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\NpmVersionValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpDeprecationValidator;
use UserFrosting\Sprinkle\Core\Validators\PhpVersionValidator;
use UserFrosting\Sprinkle\SprinkleManager;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * debug:version CLI tool.
 */
class DebugVersionCommand extends Command
{
    use WithSymfonyStyle;

    /**
     * Inject dependencies.
     *
     * @param EventDispatcherInterface            $eventDispatcher
     * @param SprinkleManager                     $sprinkleManager
     * @param PhpVersionValidator                 $phpVersionValidator
     * @param PhpDeprecationValidator             $phpDeprecationValidator
     * @param NodeVersionValidator                $nodeVersionValidator
     * @param NpmVersionValidator                 $npmVersionValidator
     * @param ResourceLocatorInterface            $locator
     */
    public function __construct(
        protected EventDispatcherInterface $eventDispatcher,
        protected SprinkleManager $sprinkleManager,
        protected PhpVersionValidator $phpVersionValidator,
        protected PhpDeprecationValidator $phpDeprecationValidator,
        protected NodeVersionValidator $nodeVersionValidator,
        protected NpmVersionValidator $npmVersionValidator,
        protected ResourceLocatorInterface $locator,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('debug:version')
             ->setDescription('Test the UserFrosting version dependencies')
             ->setHelp('This command is used to check if the various dependencies of UserFrosting are met');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title('UserFrosting Environnement Information');

        // Validate PHP, Node and npm version
        try {
            $this->phpVersionValidator->validate();
            $this->nodeVersionValidator->validate();
            $this->npmVersionValidator->validate();
        } catch (VersionCompareException $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        // Validate deprecated versions
        try {
            $this->phpDeprecationValidator->validate();
        } catch (VersionCompareException $e) {
            $this->io->warning($e->getMessage());
        }

        // Warn if .env file exists but cannot be read (e.g. wrong file permissions)
        $envPath = $this->locator->getBasePath() . '/.env';
        if (file_exists($envPath) && !is_readable($envPath)) {
            $this->io->warning(sprintf(
                'The .env file at "%s" exists but cannot be read. Check file permissions to ensure the web server process can read it.',
                $envPath
            ));
        }

        // Display environment information
        $this->io->definitionList(
            ['Framework version'  => \Composer\InstalledVersions::getPrettyVersion('userfrosting/framework')],
            ['OS Name'            => php_uname('s')],
            ['Main Sprinkle'      => $this->sprinkleManager->getMainSprinkle()->getName()],
            ['Main Sprinkle Path' => $this->locator->getBasePath()],
            ['Environment mode'   => env('UF_MODE', 'default')],
            ['PHP Version'        => $this->phpVersionValidator->getInstalled()],
            ['Node Version'       => $this->nodeVersionValidator->getInstalled()],
            ['NPM Version'        => $this->npmVersionValidator->getInstalled()]
        );

        return self::SUCCESS;
    }
}
