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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Account\Account;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterLogoutEvent;
use UserFrosting\Sprinkle\Account\Tests\AccountTestCase;
use UserFrosting\Sprinkle\Core\Testing\RefreshDatabase;

/**
 * Tests RegisterAction
 */
class LogoutActionTest extends AccountTestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    protected string $mainSprinkle = LogoutActionSprinkle::class;

    /**
     * Setup test database for controller tests
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->refreshDatabase();
    }

    public function testLogout(): void
    {
        /** @var User */
        $user = User::factory()->create();

        // We have to manually login the user first.
        /** @var Session */
        $session = $this->ci->get(Session::class);
        $session->start();

        /** @var Authenticator */
        $authenticator = $this->ci->get(Authenticator::class);
        $authenticator->login($user);
        $this->assertFalse($authenticator->guest());

        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/logout');
        $response = $this->handleRequest($request);

        // Assert response status & body
        $this->assertJsonResponse([
            'title'       => 'You have been logged out successfully.',
            'description' => '',
        ], $response);
        $this->assertResponseStatus(302, $response);
        $this->assertTrue($authenticator->guest());

        // Assert Event Redirect
        $this->assertSame('/home', $response->getHeaderLine('Location'));
    }

    /**
     * N.B.: This should be covered by AuthGuard
     */
    public function testLogoutWithNonLoggedInUser(): void
    {
        // Create request with method and url and fetch response
        $request = $this->createJsonRequest('GET', '/account/logout');
        $response = $this->handleRequest($request);

        // Assert response status
        $this->assertResponseStatus(400, $response);
    }
}

class LogoutActionSprinkle extends Account
{
    /**
     * {@inheritDoc}
     */
    public function getEventListeners(): array
    {
        return [
            UserRedirectedAfterLogoutEvent::class => [
                UserRedirectedAfterLogoutListener::class,
                UserRedirectedAfterLogoutListener2::class,
            ],
        ];
    }
}

class UserRedirectedAfterLogoutListener
{
    public function __invoke(UserRedirectedAfterLogoutEvent $event): void
    {
        $event->setRedirect('/home');
        $event->stop();
    }
}

class UserRedirectedAfterLogoutListener2
{
    public function __invoke(UserRedirectedAfterLogoutEvent $event): void
    {
        $event->setRedirect('/index'); // Won't be used, as "/home" stop propagation
    }
}
