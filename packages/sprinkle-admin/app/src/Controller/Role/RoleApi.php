<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Controller\Role;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;

/**
 * Renders a page displaying a role's information, in read-only mode.
 *
 * This checks that the currently logged-in user has permission to view the requested role's info.
 * It checks each field individually, showing only those that you have permission to view.
 * This will also try to show buttons for deleting and editing the role.
 * This page requires authentication.
 *
 * Request type: GET
 */
class RoleApi
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
     * @param RoleInterface $role     The role to display, injected by middleware.
     * @param Response      $response
     */
    public function __invoke(RoleInterface $role, Response $response): Response
    {
        $this->validateAccess($role);
        $role = $this->handle($role);
        $payload = json_encode($role, JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Validate access to the api.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(RoleInterface $role): void
    {
        // Access-controlled page
        if (!$this->authenticator->checkAccess('uri_role')) {
            throw new ForbiddenException();
        }

        /*
        // Determine fields that currentUser is authorized to view
        $fieldNames = ['name', 'slug', 'description'];

        // Fields to hide based on user's access.
        $fields = [
            'hidden' => [],
        ];

        // Determine which fields should be hidden
        foreach ($fieldNames as $field) {
            if (!$this->authenticator->checkAccess('view_role_field', [
                'role'     => $role,
                'property' => $field,
            ])) {
                $fields['hidden'][] = $field;
            }
        }

        // Determine buttons to display
        $editButtons = [
            'hidden' => [],
        ];

        if (!$this->authenticator->checkAccess('update_role_field', [
            'role'   => $role,
            'fields' => ['name', 'slug', 'description'],
        ])) {
            $editButtons['hidden'][] = 'edit';
        }

        if (!$this->authenticator->checkAccess('delete_role', [
            'role' => $role,
        ])) {
            $editButtons['hidden'][] = 'delete';
        }

        return [
            'role'            => $role,
            'fields'          => $fields,
            'tools'           => $editButtons,
            'delete_redirect' => $this->routeParser->urlFor('uri_roles'),
        ];*/
    }

    /**
     * Add or remove fields from the role object before returning it.
     * TIP : When extending this class, you can use this method to add your own fields.
     *
     * @param RoleInterface $role
     *
     * @return RoleInterface
     */
    protected function handle(RoleInterface $role): RoleInterface
    {
        // Add the user count to the role object
        $role->loadCount('users');

        return $role;
    }
}
