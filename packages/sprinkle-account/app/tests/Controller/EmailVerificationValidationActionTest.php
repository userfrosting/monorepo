<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Controller;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\EmailVerificationProvider;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;

class EmailVerificationValidationActionTest extends AccountTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testVerify(): void
    {
        /** @var User */
        $user = User::factory(['flag_verified' => false])->create();

        // Setup mock verification provider
        $emailVerification = Mockery::mock(EmailVerificationProvider::class)
            ->shouldReceive('validate')->once()->with(Mockery::any(), 'potatoCode')->andReturn(true)
            ->getMock();
        $this->ci->set(EmailVerificationProvider::class, $emailVerification);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/verify/email', [
            'email' => $user->email,
            'code'  => 'potatoCode',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'message' => 'You have successfully verified your account. You can now login.'
        ], $response);
    }

    public function testVerifyForUserAlreadyVerified(): void
    {
        /** @var User */
        $user = User::factory(['flag_verified' => true])->create();

        // Setup mock
        $emailVerification = Mockery::mock(EmailVerificationProvider::class)
            ->shouldNotReceive('validate')
            ->getMock();
        $this->ci->set(EmailVerificationProvider::class, $emailVerification);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/verify/email', [
            'email' => $user->email,
            'code'  => 'potatoCode',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Verification Exception',
            'description' => 'This verification code is not valid, or the account is already verified.',
            'status'      => '400',
        ], $response);
    }

    public function testVerifyWithFailedVerification(): void
    {
        /** @var User */
        $user = User::factory(['flag_verified' => false])->create();

        // Setup mock
        $emailVerification = Mockery::mock(EmailVerificationProvider::class)
            ->shouldReceive('validate')->once()->with(Mockery::any(), 'potatoCode')->andReturn(false)
            ->getMock();
        $this->ci->set(EmailVerificationProvider::class, $emailVerification);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/verify/email', [
            'email' => $user->email,
            'code'  => 'potatoCode',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Verification Exception',
            'description' => 'This verification code is not valid, or the account is already verified.',
            'status'      => '400',
        ], $response);
    }

    public function testVerifyWithFailedInputValidation(): void
    {
        // Setup mock
        $emailVerification = Mockery::mock(EmailVerificationProvider::class)
            ->shouldNotReceive('validate')
            ->getMock();
        $this->ci->set(EmailVerificationProvider::class, $emailVerification);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/verify/email');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Validation error',
            'description' => 'Please specify a value for <strong>Email</strong>. Please specify a value for <strong>Verification code</strong>.',
            'status'      => '400',
        ], $response);
    }

    public function testVerifyWithFailedThrottle(): void
    {
        /** @var User */
        $user = User::factory(['flag_verified' => false])->create();

        // Setup mock
        $emailVerification = Mockery::mock(EmailVerificationProvider::class)
            ->shouldNotReceive('validate')
            ->getMock();
        $this->ci->set(EmailVerificationProvider::class, $emailVerification);

        // Create fake throttler
        $throttler = Mockery::mock(Throttler::class)
            ->shouldReceive('getDelay')->once()->with('account.verify.email', ['email' => $user->email])->andReturn(90)
            ->getMock();
        $this->ci->set(Throttler::class, $throttler);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/verify/email', [
            'email' => $user->email,
            'code'  => 'potatoCode',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(429, $response);
    }

    public function testVerifyForDisabledVerification(): void
    {
        // Make sure email verification is required
        $config = $this->ci->get(Config::class);
        $config->set('site.registration.require_email_verification', false);
        $this->assertFalse($config->get('site.registration.require_email_verification'));

        // Setup mock
        $emailVerification = Mockery::mock(EmailVerificationProvider::class)
            ->shouldNotReceive('validate')
            ->getMock();
        $this->ci->set(EmailVerificationProvider::class, $emailVerification);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/verify/email');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
    }
}
