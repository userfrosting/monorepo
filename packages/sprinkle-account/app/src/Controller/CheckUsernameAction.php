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

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottlerDelayException;

/**
 * Check a username for availability.
 *
 * This route is throttled by default, to discourage abusing it for account enumeration.
 * This route is "public access".
 *
 * Middleware: none
 * Route: /account/check-username
 * Route Name: account.check-username
 * Request type: GET
 */
class CheckUsernameAction
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Throttler $throttler,
        protected Translator $translator,
        protected UserInterface $userModel,
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
        $payload = $this->handle($request);
        $payload = json_encode($payload, JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request and return the payload.
     *
     * @param Request $request
     *
     * @return array<string, string|bool>
     */
    protected function handle(Request $request): array
    {
        // Throttle requests.
        $this->throttle();

        // GET parameters
        $params = $request->getQueryParams();

        // Log throttle-able event
        $this->throttler->logEvent('check_username_request');

        // Condition : Username already exists
        if (isset($params['user_name'])
            && is_string($params['user_name'])
            && $this->userModel::findUnique($params['user_name'], 'user_name') !== null) {
            $message = $this->translator->translate('USERNAME.NOT_AVAILABLE', $params);

            return [
                'available' => false,
                'message'   => $message,
            ];
        }

        return [
            'available' => true,
            'message'   => '',
        ];
    }

    /**
     * Throttle requests.
     */
    protected function throttle(): void
    {
        $delay = $this->throttler->getDelay('check_username_request');
        if ($delay > 0) {
            $e = new ThrottlerDelayException();
            $e->setDelay($delay);

            throw $e;
        }
    }
}
