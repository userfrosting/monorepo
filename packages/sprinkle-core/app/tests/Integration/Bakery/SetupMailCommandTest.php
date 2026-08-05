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

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Bakery\SetupMailCommand;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;
use UserFrosting\UniformResourceLocator\ResourceStream;

/**
 * Test for SetupMailCommand (setup:env)
 */
class SetupMailCommandTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        // Use test location
        $envStream = new ResourceStream('sprinkles', path: 'env', shared: true);
        $locator = new ResourceLocator(__DIR__ . '/data');
        $locator->addStream($envStream);
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);

        // Delete existing env file
        @unlink(__DIR__ . '/data/env/.env');

        // Force config, so test env is not used
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('mail.mailer', 'smtp');
        $config->set('mail.host', '');
        $config->set('mail.port', '');
        $config->set('mail.auth', '');
        $config->set('mail.secure', '');
        $config->set('mail.username', '');
        $config->set('mail.password', '');

        // Delete env file to make sure previous state doesn't interfere.
        @unlink(__DIR__ . '/data/env/.env');
        $this->assertFileDoesNotExist(__DIR__ . '/data/env/.env');
    }

    public function testEnvNotFound(): void
    {
        // Force locator return with Mockery
        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('findResource')->andReturn(null);
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);

        // Run command and assert it fails
        /** @var SetupMailCommand */
        $command = $this->getService(SetupMailCommand::class);
        $result = BakeryTester::runCommand($command);
        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Could not find .env file', $result->getDisplay());
    }

    /**
     * WARNING : This test doesn't work on Windows.
     * @see https://symfony.com/doc/current/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     * "On Windows systems Symfony uses a special binary to implement hidden
     * questions. This means that those questions don't use the default Input
     * console object and therefore you can't test them on Windows."
     *
     * @group windows-skip
     */
    public function testCommand(): void
    {
        /** @var SetupMailCommand */
        $command = $this->getService(SetupMailCommand::class);
        $result = BakeryTester::runCommand($command, userInput: [
            '0', // smtp
            'smtp.test.com', // host
            'user', // username
            'password', // password
            '123', // port
            'y', // auth
            '2', // other
            'abs', // other (custom)
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Mail configuration saved', $result->getDisplay());

        // Assert everything is written to env file
        $dotenvEditor = new DotenvEditor();
        $dotenvEditor->load('sprinkles://.env');
        $this->assertSame('smtp', $dotenvEditor->getValue('MAIL_MAILER'));
        $this->assertSame('smtp.test.com', $dotenvEditor->getValue('SMTP_HOST'));
        $this->assertSame('user', $dotenvEditor->getValue('SMTP_USER'));
        $this->assertSame('password', $dotenvEditor->getValue('SMTP_PASSWORD'));
        $this->assertSame('123', $dotenvEditor->getValue('SMTP_PORT'));
        $this->assertSame('true', $dotenvEditor->getValue('SMTP_AUTH'));
        $this->assertSame('abs', $dotenvEditor->getValue('SMTP_SECURE'));
    }

    public function testCommandWithOptionsAndVerbose(): void
    {
        /** @var SetupMailCommand */
        $command = $this->getService(SetupMailCommand::class);
        $result = BakeryTester::runCommand($command, verbosity: OutputInterface::VERBOSITY_VERBOSE, input: [
            '--smtp_host'     => 'smtp.test.com',
            '--smtp_user'     => 'user',
            '--smtp_password' => 'password',
            '--smtp_port'     => '123',
            '--smtp_auth'     => 'y',
            '--smtp_secure'   => 'abs',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Mail configuration saved', $result->getDisplay());
        $this->assertStringContainsString('Mail configuration and SMTP credentials will be saved in', $result->getDisplay());

        // Assert everything is written to env file
        $dotenvEditor = new DotenvEditor();
        $dotenvEditor->load('sprinkles://.env');
        $this->assertSame('smtp', $dotenvEditor->getValue('MAIL_MAILER'));
        $this->assertSame('smtp.test.com', $dotenvEditor->getValue('SMTP_HOST'));
        $this->assertSame('user', $dotenvEditor->getValue('SMTP_USER'));
        $this->assertSame('password', $dotenvEditor->getValue('SMTP_PASSWORD'));
        $this->assertSame('123', $dotenvEditor->getValue('SMTP_PORT'));
        $this->assertSame('true', $dotenvEditor->getValue('SMTP_AUTH'));
        $this->assertSame('abs', $dotenvEditor->getValue('SMTP_SECURE'));
    }

    public function testCommandForNativeMethod(): void
    {
        /** @var SetupMailCommand */
        $command = $this->getService(SetupMailCommand::class);
        $result = BakeryTester::runCommand($command, input: ['--force' => true], userInput: [
            '2', // native
            'no', // Don't confirm, and start again
            '2',
            'yes',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Mail configuration saved', $result->getDisplay());
    }

    public function testCommandForNoneMethod(): void
    {
        /** @var SetupMailCommand */
        $command = $this->getService(SetupMailCommand::class);
        $result = BakeryTester::runCommand($command, input: ['--force' => true], userInput: [
            '3',
            'no', // Don't confirm, and start again
            '3',
            'yes',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Mail configuration saved', $result->getDisplay());
    }

    /**
     * WARNING : This test doesn't work on Windows.
     * @see https://symfony.com/doc/current/components/console/helpers/questionhelper.html#testing-a-command-that-expects-input
     * "On Windows systems Symfony uses a special binary to implement hidden
     * questions. This means that those questions don't use the default Input
     * console object and therefore you can't test them on Windows."
     *
     * @group windows-skip
     */
    public function testCommandForGmailMethod(): void
    {
        /** @var SetupMailCommand */
        $command = $this->getService(SetupMailCommand::class);
        $result = BakeryTester::runCommand($command, input: ['--force' => true], userInput: [
            '1', // gmail
            'test@gmail.com',
            'password',
        ]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Mail configuration saved', $result->getDisplay());
    }

    public function testCommandForAlreadyConfigured(): void
    {
        // Force env to trigger warning
        $dotenvEditor = new DotenvEditor();
        $dotenvEditor->load(__DIR__ . '/data/env/.env');
        $dotenvEditor->setKey('SMTP_HOST', 'smtp.foo');
        $dotenvEditor->save();

        /** @var SetupMailCommand */
        $command = $this->getService(SetupMailCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Mail appears to be already setup in .env file.', $result->getDisplay());
    }

    public function testCommandForAlreadyConfiguredNonSMTP(): void
    {
        // Force env to trigger warning
        $dotenvEditor = new DotenvEditor();
        $dotenvEditor->load(__DIR__ . '/data/env/.env');
        $dotenvEditor->setKey('MAIL_MAILER', 'mail');
        $dotenvEditor->save();

        /** @var SetupMailCommand */
        $command = $this->getService(SetupMailCommand::class);
        $result = BakeryTester::runCommand($command);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Mail appears to be already setup in .env file.', $result->getDisplay());
    }

    public function testCommandForDifferentEnvFromConfig(): void
    {
        // Add an env file, so we can compare the config to it
        $dotenvEditor = new DotenvEditor();
        $dotenvEditor->load(__DIR__ . '/data/env/.env');
        $dotenvEditor->setKey('SMTP_HOST', 'smtp.foo');
        $dotenvEditor->save();

        // Force config to not obey env to trigger warning
        /** @var Config */
        $config = $this->getService(Config::class);
        $config->set('mail.host', 'smtp.bar');

        // Need to force, as dotenv is present
        /** @var SetupMailCommand */
        $command = $this->getService(SetupMailCommand::class);
        $result = BakeryTester::runCommand($command, input: ['--force' => true]);

        // Assertions
        $this->assertSame(0, $result->getStatusCode());
        $this->assertStringContainsString('Current mail configuration from config service differ', $result->getDisplay());
    }
}
