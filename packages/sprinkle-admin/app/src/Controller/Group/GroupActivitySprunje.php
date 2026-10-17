<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Controller\Group;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Admin\Sprunje\ActivitySprunje;

/**
 * Returns activity history for a single group.
 *
 * This page requires authentication.
 * Request type: GET
 */
class GroupActivitySprunje
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Authenticator $authenticator,
        protected ActivitySprunje $sprunje,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param GroupInterface $group    The group, injected by the middleware.
     * @param Request        $request
     * @param Response       $response
     */
    public function __invoke(GroupInterface $group, Request $request, Response $response): Response
    {
        // Access-controlled page based on the group.
        $this->validateAccess($group);

        // GET parameters and pass to Sprunje
        $params = $request->getQueryParams();
        $this->sprunje->setOptions($params);
        $this->sprunje->forAny(context: $group, subject: $group);

        // Be careful how you consume this data - it has not been escaped and contains untrusted user-supplied content.
        // For example, if you plan to insert it into an HTML DOM, you must escape it on the client side (or use client-side templating).
        return $this->sprunje->toResponse($response);
    }

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(GroupInterface $group): void
    {
        if (!$this->authenticator->checkAccess('view_group_field')) {
            throw new ForbiddenException();
        }
    }
}
