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

use Illuminate\Database\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Admin\Exceptions\GroupException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;
use UserFrosting\Support\Message\UserMessage;

/**
 * Processes the request to delete an existing group.
 *
 * Deletes the specified group.
 * Before doing so, checks that:
 * 1. The user has permission to delete this group;
 * 2. The group is not currently set as the default for new users;
 * 3. The group is empty (does not have any users);
 * 4. The submitted data is valid.
 * This route requires authentication (and should generally be limited to admins or the root user).
 *
 * Request type: DELETE
 */
class GroupDeleteAction
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
     * @param GroupInterface $group    The group to delete, injected from middleware.
     * @param Response       $response
     */
    public function __invoke(GroupInterface $group, Response $response): Response
    {
        $userMessage = $this->handle($group);

        // Message
        $message = $this->translator->translate($userMessage->message, $userMessage->parameters);

        // Write response
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request.
     *
     * @param GroupInterface $group
     */
    protected function handle(GroupInterface $group): UserMessage
    {
        // Access-controlled page based on the group.
        $this->validateAccess($group);

        // Check that we are not deleting the default group
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if ($group->slug == $this->config->get('site.registration.user_defaults.group')) {
            $e = new GroupException();
            $message = new UserMessage('GROUP.DELETE_DEFAULT', $group->toArray());
            $e->setDescription($message);

            throw $e;
        }

        // Check if there are any users in this group
        // @phpstan-ignore-next-line False negative from Laravel
        if ($group->users()->count() > 0) {
            $e = new GroupException();
            $message = new UserMessage('GROUP.NOT_EMPTY', $group->toArray());
            $e->setDescription($message);

            throw $e;
        }

        // Alias current user for convenience. Won't be null, since it's AuthGuarded.
        /** @var UserInterface $currentUser */
        $currentUser = $this->authenticator->user();

        // Begin transaction - DB will be rolled back if an exception occurs
        $this->db->transaction(function () use ($group, $currentUser) {
            $group->delete();

            // Create activity record
            $this->userActivityLogger->info("User {$currentUser->user_name} deleted group {$group->name}.", [
                'type'    => 'group_delete',
                'user_id' => $currentUser->id,
            ]);
        });

        return new UserMessage('GROUP.DELETION_SUCCESSFUL', [
            'name' => $group->name,
        ]);
    }

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(GroupInterface $group): void
    {
        if (!$this->authenticator->checkAccess('delete_group')) {
            throw new ForbiddenException();
        }
    }
}
