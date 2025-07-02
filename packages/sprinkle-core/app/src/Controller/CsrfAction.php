<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Core\Csrf\CsrfGuard;

/**
 * Returns a CSRF token in the response headers.
 *
 * Route: /api/csrf
 * Route Name: api.csrf
 * Request type: GET
 */
class CsrfAction
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected CsrfGuard $csrf,
    ) {
    }

    /**
     * Return CSRF token headers in the response.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __invoke(Request $request, Response $response): Response
    {
        return $response->withHeader('Content-Type', 'application/json')
                        ->withHeader($this->csrf->getTokenNameKey(), $this->csrf->getTokenName() ?? '')
                        ->withHeader($this->csrf->getTokenValueKey(), $this->csrf->getTokenValue() ?? '');
    }
}
