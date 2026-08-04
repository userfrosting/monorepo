<?php

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
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Util\Captcha;

/**
 * Tests RegisterAction
 */
class RegisterActionTest extends AccountTestCase
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

    public function testWithLoggedInUser(): void
    {
        // Mock user is logged in
        $auth = Mockery::mock(Authenticator::class)
            ->shouldReceive('check')->once()->andReturn(true)
            ->getMock();
        $this->ci->set(Authenticator::class, $auth);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Already Logged-in',
            'description' => "Can't access this resource, as you're already logged-in",
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }

    public function testWithDisabledRegistration(): void
    {
        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('site.registration.enabled', false);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Registration error',
            'description' => "We're sorry, account registration has been disabled.",
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }

    public function testWithFailedHoneypot(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Registration error',
            'description' => 'A problem was encountered during the account registration process.',
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }

    public function testWithNoMasterId(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register', [
            'spiderbro' => 'http://',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Registration error',
            'description' => 'You cannot register an account until the master account has been created!',
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }

    public function testWithFailedCaptcha(): void
    {
        $this->setMasterUser();

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register', [
            'spiderbro' => 'http://',
        ]);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Registration error',
            'description' => 'You did not enter the captcha code correctly.',
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }

    public function testRegister(): void
    {
        $this->setMasterUser();
        $captcha = $this->getCaptcha();
        $this->forceLocaleConfig();
        $this->setRequireEmailVerification(false);

        // Set POST data
        $data = [
            'spiderbro'     => 'http://',
            'captcha'       => $captcha->getCaptcha(),
            'user_name'     => 'RegisteredUser',
            'first_name'    => 'Testing',
            'last_name'     => 'Register',
            'email'         => 'testRegister@test.com',
            'password'      => 'FooBarFooBar123',
            'passwordc'     => 'FooBarFooBar123',
            'locale'        => '',
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonStructure(['title', 'description'], $response);
        $this->assertJsonResponse('You have successfully registered. You can now sign in.', $response, 'title');

        // Make sure the user is added to the db by querying it
        /** @var User */
        $user = User::where('email', 'testRegister@test.com')->first();
        $this->assertSame('RegisteredUser', $user['user_name']);
        $this->assertSame('en_US', $user['locale']);
    }

    public function testRegisterWithFailedValidation(): void
    {
        $this->setMasterUser();
        $captcha = $this->getCaptcha();
        $this->forceLocaleConfig();
        $this->setRequireEmailVerification(false);

        // Set POST data
        $data = [
            'spiderbro'     => 'http://',
            'captcha'       => $captcha->getCaptcha(),
            'user_name'     => 'RegisteredUser',
            'first_name'    => 'Testing',
            'last_name'     => 'Register',
            'email'         => 'foo', // <-- Bad Email on purpose
            'password'      => 'FooBarFooBar123',
            'passwordc'     => 'FooBarFooBar123',
            'locale'        => '',
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'Validation error',
            'description' => 'Invalid email address.',
            'status'      => 400,
        ], $response);
        $this->assertResponseStatus(400, $response);
    }

    public function testRegisterWithEmailVerification(): void
    {
        /** @var Mockery\MockInterface&Mailer */
        $mailer = Mockery::mock(Mailer::class)
            ->makePartial()
            ->shouldReceive('send')->once()
            ->getMock();
        $this->ci->set(Mailer::class, $mailer);

        $this->setMasterUser();
        $captcha = $this->getCaptcha();
        $this->forceLocaleConfig();
        $this->setRequireEmailVerification(true);

        // Set POST data
        $data = [
            'spiderbro'     => 'http://',
            'captcha'       => $captcha->getCaptcha(),
            'user_name'     => 'RegisteredUser',
            'first_name'    => 'Testing',
            'last_name'     => 'Register',
            'email'         => 'testRegister@test.com',
            'password'      => 'FooBarFooBar123',
            'passwordc'     => 'FooBarFooBar123',
            'locale'        => '',
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(200, $response);
        $this->assertJsonStructure(['title', 'description'], $response);
        $this->assertJsonResponse('You have successfully registered. A link to activate your account has been sent to <strong>testRegister@test.com</strong>. You will not be able to sign in until you complete this step.', $response, 'title');
    }

    public function testRegisterWithFailedEmailVerification(): void
    {
        /** @var Mockery\MockInterface&Mailer */
        $mailer = Mockery::mock(Mailer::class)
            ->makePartial()
            ->shouldReceive('send')->once()
            ->andThrow(PHPMailerException::class)
            ->getMock();
        $this->ci->set(Mailer::class, $mailer);

        $this->setMasterUser();
        $captcha = $this->getCaptcha();
        $this->forceLocaleConfig();
        $this->setRequireEmailVerification(true);

        // Set POST data
        $data = [
            'spiderbro'     => 'http://',
            'captcha'       => $captcha->getCaptcha(),
            'user_name'     => 'RegisteredUser',
            'first_name'    => 'Testing',
            'last_name'     => 'Register',
            'email'         => 'testRegister@test.com',
            'password'      => 'FooBarFooBar123',
            'passwordc'     => 'FooBarFooBar123',
            'locale'        => '',
        ];

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register', $data);
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertResponseStatus(400, $response);
        $this->assertJsonResponse('An error occurred while sending the verification email. Please contact your administrator.', $response, 'description');
    }

    public function testRegisterWithFailedThrottle(): void
    {
        // Create fake throttler
        $throttler = Mockery::mock(Throttler::class);
        $throttler->shouldReceive('getDelay')->once()->with('registration_attempt')->andReturn(90);
        $this->ci->set(Throttler::class, $throttler);

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('POST', '/account/register', []);
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(429, $response);
    }

    /**
     * Create a "master user" and set it's id as the master user.
     */
    protected function setMasterUser(): void
    {
        /** @var User */
        $masterUser = User::factory()->create();

        /** @var Config */
        $config = $this->ci->get(Config::class);
        $config->set('reserved_user_ids.master', $masterUser->id);
    }

    /**
     * Create captcha and init random code.
     *
     * @return Captcha
     */
    protected function getCaptcha(): Captcha
    {
        /** @var Config */
        $config = $this->ci->get(Config::class);

        /** @var Captcha */
        $captcha = $this->ci->get(Captcha::class);
        $captcha->setKey(strval($config->get('session.keys.captcha')));
        $captcha->generateRandomCode();

        return $captcha;
    }

    /**
     * Force specified locale as config.
     *
     * @param string $locale
     */
    protected function forceLocaleConfig(string $locale = 'en_US'): void
    {
        /** @var Config */
        $config = $this->ci->get(Config::class);

        $config->set('site.registration.user_defaults.locale', $locale);
        $config->set('site.locales.available', [$locale => true]);
    }

    /**
     * Set require_email_verification config.
     *
     * @param bool $value
     */
    protected function setRequireEmailVerification(bool $value): void
    {
        /** @var Config */
        $config = $this->ci->get(Config::class);

        $config->set('site.registration.require_email_verification', $value);
    }
}
