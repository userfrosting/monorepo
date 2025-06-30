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

use Illuminate\Database\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\AccountException;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Admin\Exceptions\AccountNotFoundException;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;

/**
 * Processes the request to delete an existing user.
 *
 * Deletes the specified user, removing any existing associations.
 * Before doing so, checks that:
 * 1. You are not trying to delete the master account;
 * 2. You have permission to delete the target user's account.
 * This route requires authentication (and should generally be limited to admins or the root user).
 *
 * Request type: DELETE
 *
 * @throws ValidationException
 * @throws AccountNotFoundException If user is not found
 * @throws ForbiddenException       If user is not authorized to access page
 * @throws AccountException
 */
class UserDeleteAction
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
     * @param UserInterface $user     The user, injected by the middleware.
     * @param Response      $response
     */
    public function __invoke(UserInterface $user, Response $response): Response
    {
        $this->handle($user);

        // Message
        $message = $this->translator->translate('DELETION_SUCCESSFUL', $user->toArray());

        // Write response
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request.
     *
     * @param UserInterface $user
     */
    protected function handle(UserInterface $user): void
    {
        // Access-controlled page based on the user.
        $this->validateAccess($user);

        // Check that we are not deleting the master account
        // Need to use loose comparison for now, because some DBs return `id` as a string
        if ($user->id === $this->config->getInt('reserved_user_ids.master')) {
            $e = new AccountException();
            $e->setTitle('DELETE_MASTER');

            throw $e;
        }

        // Backup username for logging
        $username = $user->user_name;

        // Alias current user for convenience. Won't be null, since it's AuthGuarded.
        /** @var UserInterface $currentUser */
        $currentUser = $this->authenticator->user();

        // Begin transaction - DB will be rolled back if an exception occurs
        $this->db->transaction(function () use ($user, $username, $currentUser) {
            $user->delete();

            // Create activity record
            $this->userActivityLogger->info("User {$currentUser->user_name} deleted the account for {$username}.", [
                'type'    => 'account_delete',
                'user_id' => $currentUser->id,
            ]);
        });
    }

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(UserInterface $user): void
    {
        if (!$this->authenticator->checkAccess('delete_user')) {
            throw new ForbiddenException();
        }

        // Make sure the user is not a master user
        if ($user->id === $this->config->getInt('reserved_user_ids.master')) {
            $e = new AccountException();
            $e->setTitle('DELETE_MASTER');

            throw $e;
        }
    }
}
