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
use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\EmailVerificationProvider;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

class ForgetPasswordSetPasswordActionTest extends AccountTestCase
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

    public function testSetPassword(): void
    {
        /** @var User */
        $user = User::factory()->create();

        // Setup mock verification provider
        $emailVerification = Mockery::mock(EmailVerificationProvider::class)
            ->shouldReceive('validate')->once()->with(Mockery::any(), 'potatoCode')->andReturn(true)
            ->getMock();
        $this->getContainer()->set(EmailVerificationProvider::class, $emailVerification);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/forgot-password/set-password', [
            'email'     => $user->email,
            'password'  => 'testSetPassword',
            'passwordc' => 'testSetPassword',
            'code'      => 'potatoCode',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonResponse([
            'title'       => 'Account password updated',
            'description' => '',
        ], $response);
    }

    public function testSetPasswordWithFailedVerification(): void
    {
        /** @var User */
        $user = User::factory()->create();

        // Setup mock
        $emailVerification = Mockery::mock(EmailVerificationProvider::class)
            ->shouldReceive('validate')->once()->with(Mockery::any(), 'potatoCode')->andReturn(false)
            ->getMock();
        $this->getContainer()->set(EmailVerificationProvider::class, $emailVerification);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/forgot-password/set-password', [
            'email'     => $user->email,
            'password'  => 'testSetPassword',
            'passwordc' => 'testSetPassword',
            'code'      => 'potatoCode',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse([
            'title'       => 'Invalid Password Reset Token',
            'description' => 'This password reset request could not be found, or has expired.',
            'status'      => '400',
        ], $response);
    }

    public function testSetPasswordWithFailedValidation(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/forgot-password/set-password');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse('Validation error', $response, 'title');
        $this->assertResponseStatus(400, $response);
    }
}
