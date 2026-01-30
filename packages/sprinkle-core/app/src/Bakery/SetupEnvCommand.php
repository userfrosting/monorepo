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

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Helper command to setup the 'UF_MODE' var of the .env file.
 */
class SetupEnvCommand extends Command
{
    use WithSymfonyStyle;

    /**
     * @var string Path to the .env file
     */
    protected string $envPath = 'sprinkles://.env';

    /**
     * @var string Key for the env mode setting
     */
    protected string $modeKey = 'UF_MODE';

    /**
     * @var string[] Possible values for the env mode choice.
     *
     * N.B.: "Other..." will be added later, so users can add their own mode.
     */
    protected array $modes = [
        'default',
        'production',
        'debug',
    ];

    /**
     * @var string Default value for the env mode choice.
     */
    protected string $defaultMode = 'default';

    /**
     * Inject services.
     */
    public function __construct(
        protected ResourceLocatorInterface $locator,
        protected DotenvEditor $dotenvEditor,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        // Wrap method in a try catch to suppress any errors.
        // Exception will be properly formatted when the command is run.
        try {
            $path = $this->getEnvPath();
        } catch (Exception $e) {
            $path = 'app/.env';
        }

        $this->setName('setup:env')
             ->setDescription('UserFrosting Environment Configuration Wizard')
             ->setHelp("Helper command to setup environment mode. This can also be done manually by editing the <comment>$path</comment> file or using global server environment variables.")
             ->addOption('mode', null, InputOption::VALUE_OPTIONAL, 'The environment to use');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title("UserFrosting's Environment Setup Wizard");

        // Get env file path
        try {
            $envPath = $this->getEnvPath();
        } catch (Exception $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        // Display debug data in verbose mode.
        if ($this->io->isVerbose()) {
            $this->io->note("Environment mode will be saved in `$envPath`");
        }

        // Get an instance of the DotenvEditor and load the env file
        $this->dotenvEditor->load($envPath);

        // Ask for mode
        $newEnvMode = $this->askForEnv($input);

        // Save new value
        $this->dotenvEditor->setKey($this->modeKey, $newEnvMode);
        $this->dotenvEditor->save();

        // Success
        $this->io->success("Environment mode successfully changed to `$newEnvMode` in `$envPath`");

        return self::SUCCESS;
    }

    /**
     * Ask for env mode.
     *
     * @param InputInterface $args Command arguments
     *
     * @return string The new env mode
     */
    protected function askForEnv(InputInterface $args): string
    {
        // Ask for mode if not defined in command arguments
        if ($args->getOption('mode') == true) {
            return strval($args->getOption('mode'));
        } else {
            // Add "other" option so user can add their own mode
            $modes = array_merge($this->modes, ['Other...']);
            $this->io->note('Production should only be used when deploying a live app.');
            $this->io->write('Select desired environment mode.');
            $newEnvMode = $this->io->choice('Environment Mode', $modes, $this->defaultMode);
        }

        // Ask for manual input if 'other' was chosen
        if ($newEnvMode === 'Other...') {
            $newEnvMode = $this->io->ask('Enter desired environment mode');
        }

        return strval($newEnvMode);
    }

    /**
     * Returns the path to the .env file.
     *
     * @return string
     */
    protected function getEnvPath(): string
    {
        $path = $this->locator->findResource($this->envPath, all: true);

        if ($path === null) {
            throw new Exception('Could not find .env file');
        }

        return $path;
    }
}
