<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Authenticate;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use Twig\Environment;
use Twig\Loader\ArrayLoader;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\EmailOtpProvider;

use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserVerificationInterface;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;

/**
 * Tests for EmailOtpProvider.
 */
class EmailOtpProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSendCodeBuildsTwigMessageAndSendsIt(): void
    {
        /** @var Mockery\MockInterface&UserInterface */
        $user = Mockery::mock(UserInterface::class);
        $user->email = 'user@example.com';
        $user->full_name = 'Test User';

        /** @var Mockery\MockInterface&UserVerificationInterface */
        $model = Mockery::mock(UserVerificationInterface::class);

        $from = [
            'email' => 'admin@example.com',
            'name'  => 'Admin',
        ];

        /** @var Mockery\MockInterface&Config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('get')->once()->with('address_book.admin')->andReturn($from)
            ->getMock();

        $environment = new Environment(new ArrayLoader([]));

        /** @var Mockery\MockInterface&\Slim\Views\Twig */
        $twig = Mockery::mock(\Slim\Views\Twig::class)
            ->shouldReceive('getEnvironment')->once()->andReturn($environment)
            ->getMock();

        /** @var Mockery\MockInterface&\UserFrosting\Sprinkle\Core\Mail\Mailer */
        $mailer = Mockery::mock(\UserFrosting\Sprinkle\Core\Mail\Mailer::class)
            ->shouldReceive('send')->once()->with(Mockery::on(
                fn ($message): bool => $message instanceof TwigMailMessage
            ))
            ->getMock();

        $provider = new TestableEmailOtpProvider($model, $config, $twig, $mailer);
        $provider->sendCodePublic($user, '123456', 120);
    }
}

/**
 * Concrete test double for EmailOtpProvider.
 */
class TestableEmailOtpProvider extends EmailOtpProvider
{
    // Make sendCode accessible for testing
    public function sendCodePublic(UserInterface $user, string $code, int $timeout): void
    {
        $this->sendCode($user, $code, $timeout);
    }
}
