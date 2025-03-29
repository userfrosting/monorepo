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

use Illuminate\Cache\Repository as Cache;
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
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Admin\Exceptions\MissingRequiredParamException;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Processes the request to update a specific field for an existing role, including permissions.
 *
 * Processes the request from the role update form, checking that:
 * 1. The logged-in user has the necessary permissions to update the putted field(s);
 * 2. The submitted data is valid.
 * This route requires authentication.
 *
 * Request type: PUT
 */
class RoleUpdateFieldAction
{
    // Request schema for client side form validation
    protected string $schema = 'schema://requests/role/edit-field.yaml';

    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected Config $config,
        protected Cache $cache,
        protected Connection $db,
        protected UserActivityLogger $userActivityLogger,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param RoleInterface $role     The role to update, injected by middleware.
     * @param string        $field    The field to update.
     * @param Request       $request
     * @param Response      $response
     */
    public function __invoke(
        RoleInterface $role,
        string $field,
        Request $request,
        Response $response
    ): Response {
        $message = $this->handle($role, $field, $request);
        $payload = json_encode([
            'message' => $this->translator->translate($message->message, $message->parameters),
        ], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request.
     *
     * @param RoleInterface $role
     * @param string        $fieldName
     * @param Request       $request
     *
     * @return UserMessage The message to display to the user.
     */
    protected function handle(
        RoleInterface $role,
        string $fieldName,
        Request $request
    ): UserMessage {
        // Access-controlled resource - check that current User has permission
        // to edit the specified field for this user
        $this->validateAccess($role, $fieldName);

        // Get current user. Won't be null, as AuthGuard prevent it
        /** @var UserInterface */
        $currentUser = $this->authenticator->user();

        // Get PUT parameters: value
        $put = (array) $request->getParsedBody();

        // Make sure data is part of $_PUT data.
        // Except for roles, which we allows to be empty.
        if (isset($put[$fieldName])) {
            $fieldData = $put[$fieldName];
        } else {
            $e = new MissingRequiredParamException();
            $e->setParam($fieldName);

            throw $e;
        }

        // Create and validate key -> value pair
        $params = [
            $fieldName => $fieldData,
        ];

        // Load the request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transformer->transform($schema, $params);

        // Validate request data
        $this->validateData($schema, $data);

        // Get validated and transformed value
        $fieldValue = $data[$fieldName];

        // Begin transaction - DB will be rolled back if an exception occurs
        $this->db->transaction(function () use ($fieldName, $fieldValue, $role, $currentUser) {
            if ($fieldName === 'permissions') {
                $role->permissions()->sync($fieldValue);

                // All user's permissions are cached. Clear cache.
                $this->cache->clear();
            } else {
                $role->$fieldName = $fieldValue; // @phpstan-ignore-line Variable property is ok here.
                $role->save();
            }

            // Create activity record
            $this->userActivityLogger->info("User {$currentUser->user_name} updated property '$fieldName' for role {$role->name}.", [
                'type'    => 'role_update_field',
                'user_id' => $currentUser->id,
            ]);
        });

        // Add success messages
        if ($fieldName === 'permissions') {
            return new UserMessage('ROLE.PERMISSIONS_UPDATED', [
                'name' => $role->name,
            ]);
        } else {
            return new UserMessage('ROLE.UPDATED', [
                'name' => $role->name,
            ]);
        }
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

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(RoleInterface $role, string $fieldName): void
    {
        if (!$this->authenticator->checkAccess('update_role_field')) {
            throw new ForbiddenException();
        }
    }
}
