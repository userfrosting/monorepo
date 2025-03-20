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

use Illuminate\Database\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Admin\Exceptions\RoleException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Processes the request to delete an existing role.
 *
 * Deletes the specified role.
 * Before doing so, checks that:
 * 1. The user has permission to delete this role;
 * 2. The role is not a default for new users;
 * 3. The role does not have any associated users;
 * 4. The submitted data is valid.
 * This route requires authentication (and should generally be limited to admins or the root user).
 *
 * Request type: DELETE
 */
class RoleDeleteAction
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected Config $config,
        protected Connection $db,
        protected UserActivityLogger $userActivityLogger,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param RoleInterface $role     The role to delete, injected from middleware.
     * @param Response      $response
     */
    public function __invoke(RoleInterface $role, Response $response): Response
    {
        $this->handle($role);
        $payload = json_encode([
            'message' => $this->translator->translate('ROLE.DELETION_SUCCESSFUL', $role->toArray()),
        ], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request.
     *
     * @param RoleInterface $role
     */
    protected function handle(RoleInterface $role): void
    {
        // Access-controlled page based on the role.
        $this->validateAccess($role);

        // TODO : Default role should be defined in the DB instead of config.
        $defaultRoleSlugs = $this->config->get('site.registration.user_defaults.roles');
        $defaultRoleSlugs = array_map('trim', array_keys($defaultRoleSlugs, true, true)); // @phpstan-ignore-line False positive on array_map

        // Need to use loose comparison for now, because some DBs return `id` as a string
        if (in_array($role->slug, $defaultRoleSlugs, true)) {
            $e = new RoleException();
            $message = new UserMessage('ROLE.DELETE_DEFAULT', $role->toArray());
            $e->setDescription($message);

            throw $e;
        }

        // Check if there are any users in this group
        // @phpstan-ignore-next-line False negative from Laravel
        if ($role->users()->count() > 0) {
            $e = new RoleException();
            $message = new UserMessage('ROLE.HAS_USERS', $role->toArray());
            $e->setDescription($message);

            throw $e;
        }

        // Alias current user for convenience. Won't be null, since it's AuthGuarded.
        /** @var UserInterface $currentUser */
        $currentUser = $this->authenticator->user();

        // Begin transaction - DB will be rolled back if an exception occurs
        $this->db->transaction(function () use ($role, $currentUser) {
            $role->delete();

            // Create activity record
            $this->userActivityLogger->info("User {$currentUser->user_name} deleted role {$role->name}.", [
                'type'    => 'role_delete',
                'user_id' => $currentUser->id,
            ]);
        });
    }

    /**
     * Validate access to the page.
     *
     * @param RoleInterface $role
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(RoleInterface $role): void
    {
        if (!$this->authenticator->checkAccess('delete_role')) {
            throw new ForbiddenException();
        }
    }
}
