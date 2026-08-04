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
use UserFrosting\Config\Config;
use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

/**
 * Helper command to setup mail config in .env file.
 */
class SetupMailCommand extends Command
{
    use WithSymfonyStyle;

    /**
     * @var string Path to the .env file
     */
    protected string $envPath = 'sprinkles://.env';

    /**
     * @var string SMTP setup string
     */
    private const Setup_SMTP = 'SMTP Server';

    /**
     * @var string Gmail setup string
     */
    private const Setup_Gmail = 'Gmail';

    /**
     * @var string Native mail setup string
     */
    private const Setup_Native = 'Native Mail';

    /**
     * @var string No email setup string
     */
    private const Setup_None = 'No email support';

    /**
     * Inject services.
     */
    public function __construct(
        protected ResourceLocatorInterface $locator,
        protected Config $config,
        protected DotenvEditor $dotenvEditor,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('setup:mail')
             ->setAliases(['setup:smtp'])
             ->setDescription('UserFrosting Mail Configuration Wizard')
             ->setHelp('Helper command to setup outgoing email configuration. This can also be done manually by editing the <comment>app/.env</comment> file or using global server environment variables.')
             ->addOption('force', null, InputOption::VALUE_NONE, 'Force setup if SMTP appears to be already configured')
             ->addOption('smtp_host', null, InputOption::VALUE_OPTIONAL, 'The SMTP server hostname')
             ->addOption('smtp_user', null, InputOption::VALUE_OPTIONAL, 'The SMTP server user')
             ->addOption('smtp_password', null, InputOption::VALUE_OPTIONAL, 'The SMTP server password')
             ->addOption('smtp_port', null, InputOption::VALUE_OPTIONAL, 'The SMTP server port')
             ->addOption('smtp_auth', null, InputOption::VALUE_OPTIONAL, 'The SMTP server authentication')
             ->addOption('smtp_secure', null, InputOption::VALUE_OPTIONAL, 'The SMTP server security type');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Display header,
        $this->io->title("UserFrosting's Mail Setup Wizard");

        // Get env file path
        try {
            $envPath = $this->getEnvPath();
        } catch (Exception $e) {
            $this->io->error($e->getMessage());

            return self::FAILURE;
        }

        // Get an instance of the DotenvEditor, then save to make sure empty
        // file is created if none exist before reading it.
        $this->dotenvEditor->load($envPath);
        $this->dotenvEditor->save();

        // Display config in verbose mode
        if ($this->io->isVerbose()) {
            $this->displayConfig();
        }

        // Get command options
        $force = (bool) $input->getOption('force');

        // Check if db is already setup
        if ($force === false && $this->isSmtpConfigured()) {
            $this->io->note('Mail appears to be already setup in .env file. Use the `php bakery setup:mail --force` command to run setup again.');

            return self::SUCCESS;
        }

        // There may be some custom config or global env values defined on the server.
        // We'll check for that and ask for confirmation in this case.
        if ($this->compareEnvToConfig()) {
            $this->io->warning("Current mail configuration from config service differ from the configuration defined in `$envPath`. Configuration might not use environment variables, or a global system environment variables might be defined. It might not be required to setup mail again.");

            if (!$this->io->confirm('Continue with mail setup?', false)) {
                return self::SUCCESS;
            }
        }

        // Display debug data in verbose mode.
        if ($this->io->isVerbose()) {
            $this->io->note("Mail configuration and SMTP credentials will be saved in `{$envPath}`");
        }

        // Ask for SMTP info
        $params = $this->askForMailMethod($input);

        // Time to save
        $this->io->section('Saving data');
        foreach ($params as $key => $value) {
            $this->dotenvEditor->setKey($key, $value);
        }
        $this->dotenvEditor->save();

        // Success
        $this->io->success("Mail configuration saved to `$envPath`.\nYou can test outgoing mail using `test:mail` command.");

        return self::SUCCESS;
    }

    /**
     * Ask with setup method to use.
     *
     * @param InputInterface $input
     *
     * @return array<string, string> The SMTP connection info
     */
    protected function askForMailMethod(InputInterface $input): array
    {
        // If the user defined any of the command argument, skip right to SMTP method
        if (($input->getOption('smtp_host') !== null && $input->getOption('smtp_host') !== '') ||
            ($input->getOption('smtp_user') !== null && $input->getOption('smtp_user') !== '') ||
            ($input->getOption('smtp_password') !== null && $input->getOption('smtp_password') !== '') ||
            ($input->getOption('smtp_port') !== null && $input->getOption('smtp_port') !== '') ||
            ($input->getOption('smtp_auth') !== null && $input->getOption('smtp_auth') !== '') ||
            ($input->getOption('smtp_secure') !== null && $input->getOption('smtp_secure') !== '')
        ) {
            return $this->askForSmtp($input);
        }

        // Display nice explanation and ask which method to use
        $this->io->write("In order to send registration emails, UserFrosting requires an outgoing mail server. When using UserFrosting in a production environment, a SMTP server should be used. A Gmail account or native mail command can be used when on a local dev environment. You can also choose to not setup an outgoing mail server at the moment, but account registration won't work. You can always re-run this command using the `--force` option or edit the configuration if you have problems sending email later.");

        // Ask which method to use
        $choice = $this->io->choice('Select setup method', [
            self::Setup_SMTP,
            self::Setup_Gmail,
            self::Setup_Native,
            self::Setup_None,
        ], self::Setup_SMTP);

        // @phpstan-ignore-next-line : $choice can only be one of the choices above, no need for default.
        return match ($choice) {
            self::Setup_SMTP   => $this->askForSmtp($input),
            self::Setup_Gmail  => $this->askForGmail($input),
            self::Setup_Native => $this->askForNative($input),
            self::Setup_None   => $this->askForNone($input),
        };
    }

    /**
     * Ask for SMTP credential.
     *
     * @param InputInterface $input Command arguments
     *
     * @return array<string, string> The SMTP connection info
     */
    protected function askForSmtp(InputInterface $input): array
    {
        // Get options
        $host = $input->getOption('smtp_host');
        $user = $input->getOption('smtp_user');
        $password = $input->getOption('smtp_password');
        $port = $input->getOption('smtp_port');
        $auth = $input->getOption('smtp_auth');
        $secure = $input->getOption('smtp_secure');

        // Validate options, if not set, ask for them// Ask for the smtp values now
        $host = ($host !== null && $host !== '') ? $host : $this->io->ask('SMTP Server Host', 'host.example.com');
        $user = ($user !== null && $user !== '') ? $user : $this->io->ask('SMTP Server User', 'relay@example.com');
        $password = ($password !== null && $password !== '') ? $password : $this->io->askHidden('SMTP Server Password', function ($password) {
            // Use custom validator to accept empty password
            return $password;
        });
        $port = ($port !== null && $port !== '') ? $port : $this->io->ask('SMTP Server Port', '587');
        $auth = ($auth !== null && $auth !== '') ? $auth : $this->io->confirm('SMTP Server Authentication', true);
        $secure = ($secure !== null && $secure !== '') ? $secure : $this->io->choice('SMTP Server Security type', ['tls', 'ssl', 'Other...'], 'tls');

        // Ask for custom input if 'other' was chosen
        if ($secure === 'Other...') {
            $secure = $this->io->ask('Enter custom SMTP Server Security type');
        }

        return [
            'MAIL_MAILER'   => 'smtp',
            'SMTP_HOST'     => strval($host),
            'SMTP_USER'     => strval($user),
            'SMTP_PASSWORD' => strval($password),
            'SMTP_PORT'     => strval($port),
            'SMTP_AUTH'     => ((bool) $auth) ? 'true' : 'false',
            'SMTP_SECURE'   => strval($secure),
        ];
    }

    /**
     * Ask for Gmail.
     *
     * @param InputInterface $input Command arguments
     *
     * @return array<string, string> The SMTP connection info
     */
    protected function askForGmail(InputInterface $input): array
    {
        // Get options
        $user = $input->getOption('smtp_user');
        $password = $input->getOption('smtp_password');

        // Validate options, if not set, ask for them
        $user = (is_string($user)) ? $user : $this->io->ask('Your full Gmail (e.g. example@gmail.com)');
        $password = (is_string($password)) ? $password : $this->io->askHidden('Your Gmail password', function ($password) {
            // Use custom validator to accept empty password
            return $password;
        });

        return [
            'MAIL_MAILER'   => 'smtp',
            'SMTP_HOST'     => 'smtp.gmail.com',
            'SMTP_USER'     => strval($user),
            'SMTP_PASSWORD' => strval($password),
        ];
    }

    /**
     * Process the "native mail" setup option.
     *
     * @param InputInterface $input
     *
     * @return array<string, string> The SMTP connection info
     */
    protected function askForNative(InputInterface $input): array
    {
        // Display big warning and confirmation
        $this->io->warning('Native mail function should only be used locally, inside containers or for development purposes.');

        if ($this->io->confirm('Continue ?', false)) {
            return [
                'MAIL_MAILER'   => 'mail',
                'SMTP_HOST'     => '',
                'SMTP_USER'     => '',
                'SMTP_PASSWORD' => '',
                'SMTP_PORT'     => '',
                'SMTP_AUTH'     => '',
                'SMTP_SECURE'   => '',
            ];
        } else {
            return $this->askForMailMethod($input);
        }
    }

    /**
     * Process the "no email support" setup option.
     *
     * @param InputInterface $input
     *
     * @return array<string, string> The SMTP connection info
     */
    protected function askForNone(InputInterface $input): array
    {
        // Display big warning and confirmation
        $this->io->warning("By not setting up any outgoing mail server, public account registration won't work.");

        if ($this->io->confirm('Continue ?', false)) {
            return [
                'MAIL_MAILER'   => 'smtp',
                'SMTP_HOST'     => '',
                'SMTP_USER'     => '',
                'SMTP_PASSWORD' => '',
                'SMTP_PORT'     => '',
                'SMTP_AUTH'     => '',
                'SMTP_SECURE'   => '',
            ];
        } else {
            return $this->askForMailMethod($input);
        }
    }

    /**
     * Check if SMTP host is defined or not, either in the env or config.
     * Note the host is the only one required to be defined in every scenario.
     *
     * @return bool true if SMTP_HOST is configured.
     */
    protected function isSmtpConfigured(): bool
    {
        $mailer = $this->dotenvEditor->keyExists('MAIL_MAILER') ? $this->dotenvEditor->getValue('MAIL_MAILER') : $this->config->get('mail.mailer');
        $host = $this->dotenvEditor->keyExists('SMTP_HOST') ? $this->dotenvEditor->getValue('SMTP_HOST') : $this->config->get('mail.host');

        if ($mailer === 'mail' || $mailer === 'smtp' && $host !== '' && $host !== null) {
            return true;
        } else {
            return false;
        }
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

    /**
     * There may be some custom config or global env values defined on the server.
     * We'll returns true if config doesn't match .env file params and ask for
     * confirmation in this case.
     *
     * @return bool
     */
    protected function compareEnvToConfig(): bool
    {
        // Get env keys. Use the config default if not defined.
        // This way, if the DOTENV is defined, it's value will be compared to
        // the config. Otherwise, the config default will be compare to itself.
        $env = [
            'MAIL_MAILER'   => ($this->dotenvEditor->keyExists('MAIL_MAILER')) ? $this->dotenvEditor->getValue('MAIL_MAILER') : $this->config->get('mail.mailer'),
            'SMTP_HOST'     => ($this->dotenvEditor->keyExists('SMTP_HOST')) ? $this->dotenvEditor->getValue('SMTP_HOST') : $this->config->get('mail.host'),
            'SMTP_USER'     => ($this->dotenvEditor->keyExists('SMTP_USER')) ? $this->dotenvEditor->getValue('SMTP_USER') : $this->config->get('mail.user'),
            'SMTP_PASSWORD' => ($this->dotenvEditor->keyExists('SMTP_PASSWORD')) ? $this->dotenvEditor->getValue('SMTP_PASSWORD') : $this->config->get('mail.password'),
            'SMTP_PORT'     => ($this->dotenvEditor->keyExists('SMTP_PORT')) ? $this->dotenvEditor->getValue('SMTP_PORT') : $this->config->get('mail.port'),
            'SMTP_AUTH'     => ($this->dotenvEditor->keyExists('SMTP_AUTH')) ? $this->dotenvEditor->getValue('SMTP_AUTH') : $this->config->get('mail.auth'),
            'SMTP_SECURE'   => ($this->dotenvEditor->keyExists('SMTP_SECURE')) ? $this->dotenvEditor->getValue('SMTP_SECURE') : $this->config->get('mail.secure'),
        ];

        return (string) $this->config->get('mail.mailer') !== (string) $env['MAIL_MAILER'] ||
            (string) $this->config->get('mail.host') !== (string) $env['SMTP_HOST'] ||
            (string) $this->config->get('mail.username') !== (string) $env['SMTP_USER'] ||
            (string) $this->config->get('mail.password') !== (string) $env['SMTP_PASSWORD'] ||
            (string) $this->config->get('mail.port') !== (string) $env['SMTP_PORT'] ||
            (string) $this->config->get('mail.auth') !== (string) $env['SMTP_AUTH'] ||
            (string) $this->config->get('mail.secure') !== (string) $env['SMTP_SECURE'];
    }

    /**
     * Display current mail config, comparing the config file to the .env file.
     */
    protected function displayConfig(): void
    {
        $this->io->section('Current Configuration');

        // Get keys
        $env = [
            'MAIL_MAILER'   => ($this->dotenvEditor->keyExists('MAIL_MAILER')) ? $this->dotenvEditor->getValue('MAIL_MAILER') : 'smtp',
            'SMTP_HOST'     => ($this->dotenvEditor->keyExists('SMTP_HOST')) ? $this->dotenvEditor->getValue('SMTP_HOST') : '',
            'SMTP_USER'     => ($this->dotenvEditor->keyExists('SMTP_USER')) ? $this->dotenvEditor->getValue('SMTP_USER') : '',
            'SMTP_PASSWORD' => ($this->dotenvEditor->keyExists('SMTP_PASSWORD')) ? '*********' : '',
            'SMTP_PORT'     => ($this->dotenvEditor->keyExists('SMTP_PORT')) ? $this->dotenvEditor->getValue('SMTP_PORT') : '',
            'SMTP_AUTH'     => ($this->dotenvEditor->keyExists('SMTP_AUTH')) ? $this->dotenvEditor->getValue('SMTP_AUTH') : '',
            'SMTP_SECURE'   => ($this->dotenvEditor->keyExists('SMTP_SECURE')) ? $this->dotenvEditor->getValue('SMTP_SECURE') : '',
        ];

        // Format Password from env & config for display
        $password = (is_string($this->config->get('mail.password')) && $this->config->get('mail.password') !== '') ? '*********' : '';

        $this->io->table(['Param', 'Config', 'Env'], [
            ['MAILER', $this->config->get('mail.mailer'), $env['MAIL_MAILER']],
            ['HOST', $this->config->get('mail.host'), $env['SMTP_HOST']],
            ['USERNAME', $this->config->get('mail.username'), $env['SMTP_USER']],
            ['PASSWORD', $password, $env['SMTP_PASSWORD']],
            ['PORT', $this->config->get('mail.port'), $env['SMTP_PORT']],
            ['AUTH', $this->config->get('mail.auth'), $env['SMTP_AUTH']],
            ['SECURE', $this->config->get('mail.secure'), $env['SMTP_SECURE']],
        ]);
    }
}
