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
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\EmailNotUniqueException;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorderInterface;
use UserFrosting\Sprinkle\Admin\Log\AdminAccountActivityTypes;
use UserFrosting\Support\Message\UserMessage;

/**
 * Processes the request to update an existing user's basic details.
 *
 * Processes the request from the user update form, checking that:
 * 1. The target user's new email address, if specified, is not already in use;
 * 2. The logged-in user has the necessary permissions to update the putted field(s);
 * 3. The submitted data is valid.
 *
 * This route requires authentication.
 * Request type: PUT
 */
class UserEditAction extends UserUpdateAction
{
    // Request schema for client side form validation
    protected string $schema = 'schema://requests/user/edit-info.yaml';

    /**
     * Inject dependencies.
     */
    public function __construct(
        Translator $translator,
        Authenticator $authenticator,
        Config $config,
        protected Connection $db,
        protected ActivityRecorderInterface $logger,
        protected UserInterface $userModel,
        RequestDataTransformer $transformer,
        ServerSideValidator $validator,
    ) {
        parent::__construct($translator, $authenticator, $config, $transformer, $validator);
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
        return $this->respond(
            $response,
            new UserMessage('DETAILS_UPDATED', $this->handle($user, $request)->toArray())
        );
    }

    /**
     * Handle the request.
     *
     * @param UserInterface $user
     * @param Request       $request
     *
     * @return UserInterface
     */
    protected function handle(UserInterface $user, Request $request): UserInterface
    {
        $currentUser = $this->authorize($user, 'update_user_field');

        // Load the request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transform($schema, $request);

        // Check if email already exists
        if (
            isset($data['email']) &&
            $data['email'] !== $user->email &&
            $this->userModel::findUnique($data['email'], 'email') !== null
        ) {
            $e = new EmailNotUniqueException();
            $e->setEmail($data['email']);

            throw $e;
        }

        // Begin transaction - DB will be rolled back if an exception occurs
        $newUser = $this->db->transaction(function () use ($data, $user, $currentUser) {
            // Update the user
            foreach ($data as $name => $value) {
                $user->setAttribute($name, $value);
            }

            // Create activity record if the subject contains dirty attributes.
            if ($user->isDirty()) {
                $this->logger->record(
                    user: $currentUser,
                    type: AdminAccountActivityTypes::UPDATE_INFO,
                    subject: $user,
                    withProperties: true,
                );
            }

            $user->save();

            return $user;
        });

        return $newUser;
    }
}
