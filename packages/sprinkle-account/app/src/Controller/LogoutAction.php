<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterLogoutEvent;
use UserFrosting\Sprinkle\Core\Csrf\CsrfGuard;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;

/**
 * Processes an account login request.
 *
 * Processes the request from the form on the login page, checking that:
 * 1. The user is not already logged in.
 * 2. The rate limit for this type of request is being observed.
 * 3. Email login is enabled, if an email address was used.
 * 4. The user account exists.
 * 5. The user account is enabled and verified.
 * 6. The user entered a valid username/email and password.
 * This route, by definition, is "public access".
 *
 * Middleware: GuestGuard
 * Route: /account/login
 * Route Name: account.login
 * Request type: POST
 */
class LogoutAction
{
    /**
     * Inject dependencies.
     *
     * @param \UserFrosting\Event\EventDispatcher $eventDispatcher
     */
    public function __construct(
        protected Authenticator $authenticator,
        protected EventDispatcherInterface $eventDispatcher,
        protected CsrfGuard $csrf,
        protected Translator $translator,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $this->handle($request);

        // Get redirect target and add Header
        $event = $this->eventDispatcher->dispatch(new UserRedirectedAfterLogoutEvent());
        if ($event->getRedirect() !== null) {
            $response = $response->withStatus(302)
                                 ->withHeader('Location', $event->getRedirect());
        }

        // Write response
        $message = $this->translator->translate('LOGGED_OUT');
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json')
                        ->withHeader($this->csrf->getTokenNameKey(), $this->csrf->getTokenName() ?? '')
                        ->withHeader($this->csrf->getTokenValueKey(), $this->csrf->getTokenValue() ?? '');
    }

    /**
     * Destroy the session.
     *
     * @param Request $request
     */
    protected function handle(Request $request): void
    {
        $this->authenticator->logout();
    }
}
