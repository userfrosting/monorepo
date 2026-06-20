<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Bakery;

use DI\Attribute\Inject;
use Exception;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Bakery\WithSymfonyStyle;
use UserFrosting\Config\Config;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Bakery\Exception\BakeryError;
use UserFrosting\Sprinkle\Account\Bakery\Exception\BakeryNote;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\RoleUsersTable;
use UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Event\UserCreatedEvent;
use UserFrosting\Sprinkle\Account\Exceptions\AccountException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLoggerInterface;
use UserFrosting\Sprinkle\Account\Log\UserActivityTypes;
use UserFrosting\Sprinkle\Account\Validators\UserValidation;
use UserFrosting\Sprinkle\Core\Bakery\Helper\DatabaseTest;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationRepositoryInterface;
use UserFrosting\Support\Message\UserMessage;

/**
 * Create user CLI command.
 */
class CreateUser extends Command
{
    use DatabaseTest;
    use WithSymfonyStyle;

    // Request schema to use to validate data.
    protected string $schema = 'schema://requests/register.yaml';

    #[Inject]
    protected MigrationRepositoryInterface $repository;

    #[Inject]
    protected UserValidation $userValidation;

    #[Inject]
    protected UserInterface $userModel;

    /**
     * @var \UserFrosting\Event\EventDispatcher
     */
    #[Inject]
    protected EventDispatcherInterface $eventDispatcher;

    #[Inject]
    protected UserActivityLoggerInterface $logger;

    #[Inject]
    protected Capsule $capsule;

    #[Inject]
    protected Config $config;

    #[Inject]
    protected Translator $translator;

    #[Inject]
    protected RequestDataTransformer $transformer;

    #[Inject]
    protected ServerSideValidator $validator;

    /**
     * @var class-string[] Migration dependencies for this command to work
     */
    protected array $dependencies = [
        UsersTable::class,
        RolesTable::class,
        RoleUsersTable::class,
    ];

    /** @var string The command name */
    protected string $commandName = 'create:user';

    /** @var string The command name */
    protected string $commandTitle = 'Creating new user account';

    /**
     * {@inheritdoc}
     *
     * @phpstan-ignore-next-line
     */
    protected function configure()
    {
        $this->setName($this->commandName)
             ->setDescription('Create a new user account.')
             ->addOption('username', null, InputOption::VALUE_OPTIONAL, 'The user username')
             ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'The user email')
             ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'The user password')
             ->addOption('firstName', null, InputOption::VALUE_OPTIONAL, 'The user first name')
             ->addOption('lastName', null, InputOption::VALUE_OPTIONAL, 'The user last name');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title($this->commandTitle);

        // Validate requirements before running command.
        try {
            $this->validateRequirements();
        } catch (BakeryError $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        } catch (BakeryNote $e) {
            $this->io->note($e->getMessage());

            return self::SUCCESS;
        }

        // Compile all the data.
        $this->io->writeln("Please answer the following questions to create the user:\n");
        $schema = $this->getSchema();
        $ignoreInputOptions = false;

        while (true) {
            $data = $this->getUserData($input, $ignoreInputOptions);

            // Load the request schema, data transformer and validate data
            $data = $this->transformer->transform($schema, $data);
            $errors = $this->validator->validate($schema, $data);
            if (count($errors) === 0) {
                break;
            }

            foreach ($errors as $error) {
                $this->io->error($error);
            }

            $this->io->note('Validation failed. Please try again.');

            // If invalid data came from CLI options, prompt the user instead of
            // looping forever with the same invalid values.
            $ignoreInputOptions = true;
        }

        // Create model and validate.
        $user = new $this->userModel($data);

        // Try validation. Exceptions will be thrown if it fails.
        try {
            $this->userValidation->validate($user);
        } catch (AccountException $e) {
            $title = $this->translateExceptionPart($e->getTitle());
            $description = $this->translateExceptionPart($e->getDescription());
            $this->io->error("$title: $description");

            return self::FAILURE;
        }

        // Ok, now we've got the info and we can create the new user.
        $this->io->write("\n<info>Saving the user data...</info>");

        $user = $this->capsule->getConnection()->transaction(function () use ($user) {
            $user->save();

            // Dispatch UserCreatedEvent
            $event = new UserCreatedEvent($user);
            $user = $this->eventDispatcher->dispatch($event)->user;

            // Create activity record
            $this->logger->info("User {$user->user_name} account was created.", [
                'type'    => UserActivityTypes::REGISTER,
                'user_id' => $user->id,
            ]);

            return $user;
        });

        $this->io->success('User creation successful!');

        return self::SUCCESS;
    }

    protected function validateRequirements(): void
    {
        // Check the database and migration status. testDB will throw an exception if it fails.
        // We then rethrow as an Bakery Error.
        try {
            $this->io->writeln('<info>Testing database connection</info>', OutputInterface::VERBOSITY_VERBOSE);
            $this->testDB();
            $this->io->writeln('Ok', OutputInterface::VERBOSITY_VERBOSE);
        } catch (Exception $e) {
            throw new BakeryError($e->getMessage());
        }

        // Need migration table
        if (!$this->repository->exists()) {
            throw new BakeryError("Migrations doesn't appear to have been run! Make sure the database is properly migrated by using the `php bakery migrate` command.");
        }

        // Make sure the required migrations have been run
        foreach ($this->dependencies as $migration) {
            if (!$this->repository->has($migration)) {
                throw new BakeryError("Migration `$migration` doesn't appear to have been run! Make sure all migrations are up to date by using the `php bakery migrate` command.");
            }
        }
    }

    /**
     * Get field from input options, or ask user to enter it otherwise.
     *
     * @param InputInterface $input
     * @param string         $field
     * @param string         $question
     * @param bool           $hidden
     * @param bool           $ignoreInputOptions
     *
     * @return string
     */
    protected function getField(
        InputInterface $input,
        string $field,
        string $question,
        bool $hidden = false,
        bool $ignoreInputOptions = false,
    ): string {
        if (!$ignoreInputOptions && is_string($input->getOption($field)) && $input->getOption($field) !== '') {
            return $input->getOption($field);
        }

        $value = ($hidden) ? $this->io->askHidden($question) : $this->io->ask($question);

        if (!is_string($value) || $value === '') {
            $this->io->error("You must enter a string value for $field");

            return $this->getField($input, $field, $question, $hidden, $ignoreInputOptions);
        }

        return $value;
    }

    /**
     * Load the request schema.
     *
     * @return RequestSchemaInterface
     */
    protected function getSchema(): RequestSchemaInterface
    {
        $schema = new RequestSchema($this->schema);
        $schema->set('password.validators.length.min', $this->config->get('site.password.length.min'));
        $schema->set('password.validators.length.max', $this->config->get('site.password.length.max'));
        $schema->set('passwordc.validators.length.min', $this->config->get('site.password.length.min'));
        $schema->set('passwordc.validators.length.max', $this->config->get('site.password.length.max'));

        return $schema;
    }

    /**
     * Get the user data from the input.
     *
     * @param InputInterface $input
     * @param bool           $ignoreInputOptions
     *
     * @return array<string, string|bool>
     */
    protected function getUserData(InputInterface $input, bool $ignoreInputOptions = false): array
    {
        return [
            'user_name'     => $this->getField($input, 'username', 'Enter username', false, $ignoreInputOptions),
            'password'      => $this->getField($input, 'password', 'Enter password', true, $ignoreInputOptions),
            'passwordc'     => $this->getField($input, 'password', 'Confirm password', true, $ignoreInputOptions),
            'email'         => $this->getField($input, 'email', 'Enter a valid email address', false, $ignoreInputOptions),
            'first_name'    => $this->getField($input, 'firstName', 'Enter first name', false, $ignoreInputOptions),
            'last_name'     => $this->getField($input, 'lastName', 'Enter last name', false, $ignoreInputOptions),
            'locale'        => $this->config->getString('site.registration.user_defaults.locale', 'en_US'),
            'flag_verified' => true,
            'flag_enabled'  => true,
        ];
    }

    /**
     * Translate a string or UserMessage to a string.
     *
     * @param string|UserMessage $message
     *
     * @return string
     */
    protected function translateExceptionPart(string|UserMessage $message): string
    {
        if ($message instanceof UserMessage) {
            return $this->translator->translate($message->message, $message->parameters);
        }

        return $this->translator->translate($message);
    }
}
