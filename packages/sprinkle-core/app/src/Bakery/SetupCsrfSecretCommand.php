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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Initialize or rotate the CSRF signing secret in the application environment.
 */
class SetupCsrfSecretCommand extends Command
{
    use WithSymfonyStyle;

    /**
     * @var string The path to the environment file
     */
    protected string $envPath = 'sprinkles://.env';

    /**
     * @var string The key for the CSRF secret in the environment file
     */
    protected string $secretKey = 'CSRF_SECRET';

    /**
     * @var int The minimum length for a manually provided secret
     */
    protected int $minimumSecretLength = 32;

    public function __construct(
        protected ResourceLocatorInterface $locator,
        protected DotenvEditor $dotenvEditor,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('setup:csrf-secret')
             ->setDescription('Initialize the CSRF signing secret')
             ->setHelp('Creates CSRF_SECRET in the application .env file. Existing secrets are preserved unless --force is used.')
             ->addArgument('secret', InputArgument::OPTIONAL, 'The CSRF secret; omit it to generate one or pass an empty value to prompt')
             ->addOption('force', 'f', InputOption::VALUE_NONE, 'Replace an existing CSRF secret');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->title('CSRF Secret Setup');

        try {
            $envPath = $this->getEnvPath();
        } catch (Exception $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        $this->dotenvEditor->load($envPath);
        $existingSecret = $this->dotenvEditor->getValue($this->secretKey);
        $force = $input->getOption('force') === true;

        if (is_string($existingSecret) && $existingSecret !== '' && !$force) {
            $this->io->success("CSRF secret is already configured in `$envPath`.");

            return self::SUCCESS;
        }

        $secret = $this->askForSecret($input);
        if ($secret === '') {
            $secret = bin2hex(random_bytes(32));
            $source = 'generated';
        } else {
            if (strlen($secret) < $this->minimumSecretLength) {
                $this->io->error("The CSRF secret must be at least {$this->minimumSecretLength} characters long.");

                return self::FAILURE;
            }
            $source = 'provided';
        }

        try {
            $this->dotenvEditor->setKey($this->secretKey, $secret);
            $this->dotenvEditor->save();
        } catch (Exception $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        $action = $force && $existingSecret !== null && $existingSecret !== '' ? 'rotated' : 'configured';
        $this->io->success("CSRF secret $action ($source) in `$envPath`.");

        return self::SUCCESS;
    }

    protected function askForSecret(InputInterface $input): string
    {
        $secret = $input->getArgument('secret');
        if (is_string($secret) && $secret !== '') {
            return trim($secret);
        }

        if ($secret !== '' || !$input->isInteractive()) {
            return '';
        }

        $secret = $this->io->askHidden("Enter CSRF secret (minimum {$this->minimumSecretLength} characters; leave empty to generate)");

        return is_string($secret) ? trim($secret) : '';
    }

    protected function getEnvPath(): string
    {
        $path = $this->locator->findResource($this->envPath, all: true);

        if ($path === null) {
            throw new Exception('Could not find .env file');
        }

        return $path;
    }
}
