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
use UserFrosting\Sprinkle\Core\Bakery\SetupCsrfSecretCommand;
use UserFrosting\Sprinkle\Core\Core;
use UserFrosting\Sprinkle\Core\Tests\CoreTestCase;
use UserFrosting\Support\DotenvEditor\DotenvEditor;
use UserFrosting\Testing\BakeryTester;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class SetupCsrfSecretCommandTest extends CoreTestCase
{
    use MockeryPHPUnitIntegration;

    protected string $envPath;

    public function setUp(): void
    {
        parent::setUp();

        $this->envPath = tempnam(sys_get_temp_dir(), 'uf-csrf-env-');
        file_put_contents($this->envPath, "UF_MODE=debug\n");

        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('findResource')->with('sprinkles://.env', true, true)->andReturn($this->envPath);
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);
    }

    public function tearDown(): void
    {
        @unlink($this->envPath);

        parent::tearDown();
    }

    public function testGeneratesSecretWhenInputIsBlank(): void
    {
        $result = BakeryTester::runCommand(
            $this->getService(SetupCsrfSecretCommand::class),
            userInput: ['']
        );

        $this->assertSame(0, $result->getStatusCode(), $result->getDisplay());
        $secret = $this->readSecret();
        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $secret);
        $this->assertStringNotContainsString($secret, $result->getDisplay());
    }

    public function testCoreRegistersCommand(): void
    {
        $this->assertContains(SetupCsrfSecretCommand::class, (new Core())->getBakeryCommands());
    }

    public function testFailsWhenEnvironmentFileCannotBeFound(): void
    {
        $locator = Mockery::mock(ResourceLocatorInterface::class);
        $locator->shouldReceive('findResource')->with('sprinkles://.env', true, true)->andReturn(null);
        $this->getContainer()->set(ResourceLocatorInterface::class, $locator);

        $result = BakeryTester::runCommand($this->getService(SetupCsrfSecretCommand::class));

        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Could not find .env file', $result->getDisplay());
    }

    public function testPreservesExistingSecret(): void
    {
        $existingSecret = str_repeat('a', 64);
        file_put_contents($this->envPath, "CSRF_SECRET=\"$existingSecret\"\n");

        $result = BakeryTester::runCommand($this->getService(SetupCsrfSecretCommand::class));

        $this->assertSame(0, $result->getStatusCode(), $result->getDisplay());
        $this->assertSame($existingSecret, $this->readSecret());
        $this->assertStringNotContainsString($existingSecret, $result->getDisplay());
    }

    public function testForceReplacesExistingSecretWithProvidedValue(): void
    {
        $existingSecret = str_repeat('a', 64);
        $providedSecret = str_repeat('b', 64);
        file_put_contents($this->envPath, "CSRF_SECRET=\"$existingSecret\"\n");

        $result = BakeryTester::runCommand(
            $this->getService(SetupCsrfSecretCommand::class),
            input: [
                '--force' => true,
                'secret'  => $providedSecret,
            ]
        );

        $this->assertSame(0, $result->getStatusCode(), $result->getDisplay());
        $this->assertSame($providedSecret, $this->readSecret());
        $this->assertStringNotContainsString($providedSecret, $result->getDisplay());
    }

    public function testPromptsWhenSecretArgumentIsEmpty(): void
    {
        $providedSecret = str_repeat('c', 64);

        $result = BakeryTester::runCommand(
            $this->getService(SetupCsrfSecretCommand::class),
            input: ['secret' => ''],
            userInput: [$providedSecret]
        );

        $this->assertSame(0, $result->getStatusCode(), $result->getDisplay());
        $this->assertSame($providedSecret, $this->readSecret());
        $this->assertStringNotContainsString($providedSecret, $result->getDisplay());
    }

    public function testRejectsShortProvidedSecret(): void
    {
        $shortSecret = str_repeat('d', 31);

        $result = BakeryTester::runCommand(
            $this->getService(SetupCsrfSecretCommand::class),
            input: ['secret' => $shortSecret]
        );

        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('at least 32 characters', $result->getDisplay());
    }

    public function testReportsEnvironmentSaveFailure(): void
    {
        $dotenvEditor = Mockery::mock(DotenvEditor::class);
        $dotenvEditor->shouldReceive('load')->with($this->envPath)->once()->andReturnSelf();
        $dotenvEditor->shouldReceive('getValue')->with('CSRF_SECRET')->once()->andReturn('');
        $dotenvEditor->shouldReceive('setKey')->with('CSRF_SECRET', Mockery::type('string'))->once()->andReturnSelf();
        $dotenvEditor->shouldReceive('save')->once()->andThrow(new \RuntimeException('Unable to save environment'));
        $this->getContainer()->set(DotenvEditor::class, $dotenvEditor);

        $result = BakeryTester::runCommand(
            $this->getService(SetupCsrfSecretCommand::class),
            input: ['secret' => str_repeat('e', 64)]
        );

        $this->assertSame(1, $result->getStatusCode());
        $this->assertStringContainsString('Unable to save environment', $result->getDisplay());
    }

    protected function readSecret(): string
    {
        $content = file_get_contents($this->envPath);
        preg_match('/^CSRF_SECRET="?([^"\n]+)"?$/m', (string) $content, $matches);

        return $matches[1] ?? '';
    }
}
