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
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;

class ResetPasswordRequestActionTest extends AccountTestCase
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

    public function testVerificationRequest(): void
    {
        /** @var Mockery\MockInterface&Mailer */
        $mailer = Mockery::mock(Mailer::class)
            ->makePartial()
            ->shouldReceive('send')->once()
            ->getMock();
        $this->getContainer()->set(Mailer::class, $mailer);

        /** @var User */
        $user = User::factory(['flag_verified' => false])->create();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/forgot-password/request', [
            'email' => $user->email,
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonStructure(['title', 'description'], $response);
        $this->assertResponseStatus(200, $response);
    }

    public function testVerificationRequestWithFailedThrottle(): void
    {
        /** @var Mockery\MockInterface&Mailer */
        $mailer = Mockery::mock(Mailer::class)
            ->makePartial()
            ->shouldNotReceive('send')
            ->getMock();
        $this->getContainer()->set(Mailer::class, $mailer);

        /** @var User */
        $user = User::factory(['flag_verified' => true])->create();

        // Create fake throttler
        $throttler = Mockery::mock(Throttler::class)
            ->shouldReceive('getDelay')->once()->with('account.password.reset.request', ['email' => $user->email])->andReturn(90)
            ->getMock();
        $this->getContainer()->set(Throttler::class, $throttler);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/forgot-password/request', [
            'email' => $user->email,
        ]);
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(429, $response);
    }

    public function testVerificationRequestWithFailedValidation(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/forgot-password/request');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
    }
}
