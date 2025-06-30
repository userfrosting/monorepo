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
use UserFrosting\Sprinkle\Admin\Exceptions\RoleException;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;
use UserFrosting\Support\Message\UserMessage;

/**
 * Processes the request to update an existing role's details.
 *
 * Processes the request from the role update form, checking that:
 * 1. The role name/slug are not already in use;
 * 2. The user has the necessary permissions to update the posted field(s);
 * 3. The submitted data is valid.
 * This route requires authentication (and should generally be limited to admins or the root user).
 *
 * Request type: PUT
 */
class RoleEditAction
{
    // Request schema for client side form validation
    protected string $schema = 'schema://requests/role/edit-info.yaml';

    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected Config $config,
        protected Connection $db,
        protected UserActivityLogger $userActivityLogger,
        protected RoleInterface $roleModel,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param RoleInterface $role     The role to update, injected from middleware.
     * @param Request       $request
     * @param Response      $response
     */
    public function __invoke(RoleInterface $role, Request $request, Response $response): Response
    {
        $role = $this->handle($role, $request)->toArray();

        // Message
        $message = $this->translator->translate('ROLE.UPDATED', $role);

        // Write response
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request.
     *
     * @param RoleInterface $role
     * @param Request       $request
     *
     * @return RoleInterface The role that was updated
     */
    protected function handle(RoleInterface $role, Request $request): RoleInterface
    {
        // Get PUT parameters
        $params = (array) $request->getParsedBody();

        // Load the request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transformer->transform($schema, $params);

        // Validate request data
        $this->validateData($schema, $data);
        if ($data['name'] !== $role->name) {
            $this->validateName($data['name']);
        }
        if ($data['slug'] !== $role->slug) {
            $this->validateSlug($data['slug']);
        }

        // Determine targeted fields
        $fieldNames = [];
        foreach ($data as $name => $value) {
            $fieldNames[] = $name;
        }

        // Access-controlled resource - check that currentUser has permission to edit submitted fields for this user
        if (!$this->authenticator->checkAccess('update_role_field')) {
            throw new ForbiddenException();
        }

        // Get current user. Won't be null, as AuthGuard prevent it
        /** @var UserInterface */
        $currentUser = $this->authenticator->user();

        // Begin transaction - DB will be rolled back if an exception occurs
        $role = $this->db->transaction(function () use ($data, $role, $currentUser) {
            // Update the user and generate success messages
            foreach ($data as $name => $value) {
                $role->setAttribute($name, $value);
            }

            $role->save();

            // Create activity record
            $this->userActivityLogger->info("User {$currentUser->user_name} updated details for role {$role->name}.", [
                'type'    => 'role_update_info',
                'user_id' => $currentUser->id,
            ]);

            return $role;
        });

        return $role;
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
     * Validate name is not already in use.
     *
     * @param string $name
     */
    protected function validateName(string $name): void
    {
        $group = $this->roleModel->where('name', $name)->first();
        if ($group !== null) {
            $e = new RoleException();
            $message = new UserMessage('ROLE.NAME_IN_USE', ['name' => $name]);
            $e->setDescription($message);

            throw $e;
        }
    }

    /**
     * Validate slug is not already in use.
     *
     * @param string $slug
     */
    protected function validateSlug(string $slug): void
    {
        $group = $this->roleModel->where('slug', $slug)->first();
        if ($group !== null) {
            $e = new RoleException();
            $message = new UserMessage('SLUG_IN_USE', ['slug' => $slug]);
            $e->setDescription($message);

            throw $e;
        }
    }
}
