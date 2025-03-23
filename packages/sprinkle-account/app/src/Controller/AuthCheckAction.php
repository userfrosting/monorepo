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
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;

/**
 * Return if the user is authenticated, and if he is, also return the user data.
 *
 * Middleware: None
 * Route: /account/authcheck
 * Route Name: account.authCheck
 * Request type: GET
 */
class AuthCheckAction
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Authenticator $authenticator,
    ) {
    }

    /**
     * Handle request and return data.
     *
     * @param Response $response
     */
    public function __invoke(Response $response): Response
    {
        $user = $this->authenticator->user();

        // Return 401 (Unauthorized) if user is not authenticated
        if ($user === null) {
            return $response->withStatus(401);
        }

        // Write the user data to the response body otherwise
        $payload = json_encode($user->apiData, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
