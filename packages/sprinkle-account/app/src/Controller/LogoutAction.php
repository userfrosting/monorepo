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
 * Processes an account logout request.
 *
 * Middleware: AuthGuard
 * Route: /account/logout
 * Route Name: account.logout
 * Request type: GET
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
