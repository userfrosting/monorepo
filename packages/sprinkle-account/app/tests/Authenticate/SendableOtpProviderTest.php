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
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\SendableOtpProvider;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserVerificationInterface;

/**
 * Tests for SendableOtpProvider.
 */
class SendableOtpProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testGenerateWithDefaultTimeout(): void
    {
        /** @var UserInterface */
        $user = Mockery::mock(UserInterface::class);

        /** @var UserVerificationInterface */
        $model = Mockery::mock(UserVerificationInterface::class)
            ->shouldReceive('forUser')->once()->with($user)->andReturnSelf()
            ->shouldReceive('delete')->once()
            ->shouldReceive('clearExpired')->once()
            ->shouldReceive('storeCode')->once()->with($user, '123456', 900)
            ->getMock();

        /** @var Config */
        $config = Mockery::mock(Config::class)
            ->shouldReceive('getInt')->once()->with('otp.timeout', 600)->andReturn(900)
            ->getMock();

        $provider = new TestableSendableOtpProvider($model, $config);
        $provider->fixedCode = '123456';

        $provider->generate($user);

        $this->assertSame($user, $provider->sentUser);
        $this->assertSame('123456', $provider->sentCode);
        $this->assertSame(900, $provider->sentTimeout);
    }

    public function testGenerateWithProvidedTimeout(): void
    {
        /** @var UserInterface */
        $user = Mockery::mock(UserInterface::class);

        /** @var UserVerificationInterface */
        $model = Mockery::mock(UserVerificationInterface::class)
            ->shouldReceive('forUser')->once()->with($user)->andReturnSelf()
            ->shouldReceive('delete')->once()
            ->shouldReceive('clearExpired')->once()
            ->shouldReceive('storeCode')->once()->with($user, '654321', 120)
            ->getMock();

        /** @var Config */
        $config = Mockery::mock(Config::class)
            ->shouldNotReceive('getInt')
            ->getMock();

        $provider = new TestableSendableOtpProvider($model, $config);
        $provider->fixedCode = '654321';

        $provider->generate($user, 120);

        $this->assertSame($user, $provider->sentUser);
        $this->assertSame('654321', $provider->sentCode);
        $this->assertSame(120, $provider->sentTimeout);
    }

    public function testValidateReturnsTrueWhenCodeIsValid(): void
    {
        /** @var UserInterface */
        $user = Mockery::mock(UserInterface::class);

        /** @var UserVerificationInterface */
        $model = Mockery::mock(UserVerificationInterface::class)
            ->shouldReceive('validateCode')->once()->with($user, '123456')->andReturn(true)
            ->getMock();

        /** @var Config */
        $config = Mockery::mock(Config::class);

        $provider = new TestableSendableOtpProvider($model, $config);

        $this->assertTrue($provider->validate($user, '123456'));
    }

    public function testValidateReturnsFalseWhenCodeIsInvalid(): void
    {
        /** @var UserInterface */
        $user = Mockery::mock(UserInterface::class);

        /** @var UserVerificationInterface */
        $model = Mockery::mock(UserVerificationInterface::class)
            ->shouldReceive('validateCode')->once()->with($user, 111111)->andReturn(false)
            ->getMock();

        /** @var Config */
        $config = Mockery::mock(Config::class);

        $provider = new TestableSendableOtpProvider($model, $config);

        $this->assertFalse($provider->validate($user, 111111));
    }

    public function testGenerateCodeReturnsSixDigitNumericString(): void
    {
        /** @var UserVerificationInterface */
        $model = Mockery::mock(UserVerificationInterface::class);

        /** @var Config */
        $config = Mockery::mock(Config::class);

        $provider = new TestableSendableOtpProvider($model, $config);

        $code = $provider->exposeGenerateCode();

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertGreaterThanOrEqual(100000, (int) $code);
        $this->assertLessThanOrEqual(999999, (int) $code);
    }
}

/**
 * Concrete test double for SendableOtpProvider.
 */
class TestableSendableOtpProvider extends SendableOtpProvider
{
    public string $fixedCode = '123456';
    public ?UserInterface $sentUser = null;
    public ?string $sentCode = null;
    public ?int $sentTimeout = null;

    protected function generateCode(): string
    {
        return $this->fixedCode;
    }

    public function exposeGenerateCode(): string
    {
        return parent::generateCode();
    }

    protected function sendCode(UserInterface $user, string $code, int $timeout): void
    {
        $this->sentUser = $user;
        $this->sentCode = $code;
        $this->sentTimeout = $timeout;
    }
}
