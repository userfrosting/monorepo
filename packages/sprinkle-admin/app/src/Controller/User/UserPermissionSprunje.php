<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Controller\User;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Admin\Sprunje\UserPermissionSprunje as SprunjeUserPermissionSprunje;

/**
 * Returns a list of effective Permissions for a specified User.
 *
 * Generates a list of permissions, optionally paginated, sorted and/or filtered.
 * This page requires authentication.
 * Request type: GET
 */
class UserPermissionSprunje
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Authenticator $authenticator,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param UserInterface $user     The user, injected by the middleware.
     * @param Request       $request
     * @param Response      $response
     */
    public function __invoke(UserInterface $user, Request $request, Response $response): Response
    {
        // Access-controlled page based on the user.
        $this->validateAccess($user);

        // GET parameters and pass to Sprunje
        $params = $request->getQueryParams();
        $sprunje = new SprunjeUserPermissionSprunje($user);
        $sprunje->setOptions($params);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $sprunje->toResponse($response);
    }

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(UserInterface $user): void
    {
        if (!$this->authenticator->checkAccess('view_user_permissions')) {
            throw new ForbiddenException();
        }
    }
}
