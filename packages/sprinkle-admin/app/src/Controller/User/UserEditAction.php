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
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\EmailNotUniqueException;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;

/**
 * Processes the request to update an existing user's basic details (first_name, last_name, email, locale, group_id).
 *
 * Processes the request from the user update form, checking that:
 * 1. The target user's new email address, if specified, is not already in use;
 * 2. The logged-in user has the necessary permissions to update the putted field(s);
 * 3. The submitted data is valid.
 *
 * This route requires authentication.
 * Request type: PUT
 */
class UserEditAction
{
    // Request schema for client side form validation
    protected string $schema = 'schema://requests/user/edit-info.yaml';

    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected Config $config,
        protected Connection $db,
        protected UserActivityLogger $userActivityLogger,
        protected UserInterface $userModel,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator,
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
        $user = $this->handle($user, $request)->toArray();

        // Message
        $message = $this->translator->translate('DETAILS_UPDATED', $user);

        // Write response
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
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
        // Get PUT parameters
        $params = (array) $request->getParsedBody();

        // Load the request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transformer->transform($schema, $params);

        // Validate request data
        $this->validateData($schema, $data);

        // Determine targeted fields
        $fieldNames = [];
        foreach ($data as $name => $value) {
            if ($name === 'first_name' || $name === 'last_name') {
                $fieldNames[] = 'name';
            } elseif ($name === 'group_id') {
                $fieldNames[] = 'group';
            } else {
                $fieldNames[] = $name;
            }
        }

        // Access-controlled resource - check that currentUser has permission to edit submitted fields for this user
        if (!$this->authenticator->checkAccess('update_user_field')) {
            throw new ForbiddenException();
        }

        // Get current user. Won't be null, as AuthGuard prevent it
        /** @var UserInterface */
        $currentUser = $this->authenticator->user();

        // Only the master account can edit the master account!
        if (
            ($user->id === $this->config->get('reserved_user_ids.master')) &&
            ($currentUser->id !== $this->config->get('reserved_user_ids.master'))
        ) {
            throw new ForbiddenException();
        }

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

        // Unset group relation if group_id is 0
        if (isset($data['group_id']) && $data['group_id'] == 0) {
            $data['group_id'] = null;
        }

        // Begin transaction - DB will be rolled back if an exception occurs
        $newUser = $this->db->transaction(function () use ($data, $user, $currentUser) {
            // Update the user and generate success messages
            foreach ($data as $name => $value) {
                $user->setAttribute($name, $value);
            }

            $user->save();

            // Create activity record
            $this->userActivityLogger->info("User {$currentUser->user_name} updated basic account info for user {$user->user_name}.", [
                'type'    => 'account_update_info',
                'user_id' => $user->id,
            ]);

            return $user;
        });

        return $newUser;
    }

    /**
     * Load the request schema.
     *
     * @return RequestSchemaInterface
     */
    protected function getSchema(): RequestSchemaInterface
    {
        $schema = new RequestSchema($this->schema);

        return $schema;
    }

    /**
     * Validate request POST data.
     *
     * @param RequestSchemaInterface $schema
     * @param mixed[]                $data
     */
    protected function validateData(RequestSchemaInterface $schema, array $data): void
    {
        $errors = $this->validator->validate($schema, $data);
        if (count($errors) !== 0) {
            $e = new ValidationException();
            $e->addErrors($errors);

            throw $e;
        }
    }
}
