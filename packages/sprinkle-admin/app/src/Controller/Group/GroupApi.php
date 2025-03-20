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

/**
 * Returns the group as an JSON endpoint.
 *
 * This checks that the currently logged-in user has permission to view the requested group's info.
 * This page requires authentication.
 *
 * Request type: GET
 */
class GroupApi
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
     * @param GroupInterface $group    The group to display, injected by the middleware.
     * @param Response       $response
     */
    public function __invoke(GroupInterface $group, Response $response): Response
    {
        $this->validateAccess($group);
        $group = $this->handle($group);
        $payload = json_encode($group, JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Add or remove fields from the group object before returning it.
     * TIP : When extending this class, you can use this method to add your own fields.
     *
     * @param GroupInterface $group
     *
     * @return GroupInterface
     */
    protected function handle(GroupInterface $group): GroupInterface
    {
        // Add the user count to the group object
        $group->loadCount('users');

        return $group;
    }

    /**
     * Validate access to the api.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(GroupInterface $group): void
    {
        // Has access to all groups
        if ($this->authenticator->checkAccess('uri_group')) {
            return;
        }

        // Allow access to group owner
        if ($this->authenticator->checkAccess('uri_group_own') &&
            $group->id === $this->authenticator->user()?->group_id) {
            return;
        }

        throw new ForbiddenException();
    }
}
