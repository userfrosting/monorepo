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
 * Processes the request to create a new role.
 *
 * Processes the request from the role creation form, checking that:
 * 1. The role name and slug are not already in use;
 * 2. The user has permission to create a new role;
 * 3. The submitted data is valid.
 * This route requires authentication (and should generally be limited to admins or the root user).
 *
 * Request type: POST
 */
class RoleCreateAction
{
    // Request schema for client side form validation
    protected string $schema = 'schema://requests/role/create.yaml';

    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected Connection $db,
        protected RoleInterface $roleModel,
        protected UserActivityLogger $userActivityLogger,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $this->validateAccess();
        $role = $this->handle($request)->toArray();

        // Message
        $message = $this->translator->translate('ROLE.CREATION_SUCCESSFUL', $role);

        // Write response
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request.
     *
     * @param Request $request
     *
     * @return RoleInterface
     */
    protected function handle(Request $request): RoleInterface
    {
        // Get POST parameters.
        $params = (array) $request->getParsedBody();

        // Load the request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transformer->transform($schema, $params);

        // Validate request data
        $this->validateData($schema, $data);
        $this->validateName($data['name']);
        $this->validateSlug($data['slug']);

        // Get current user. Won't be null, as AuthGuard prevent it
        /** @var UserInterface */
        $currentUser = $this->authenticator->user();

        // All checks passed!  log events/activities and create role
        // Begin transaction - DB will be rolled back if an exception occurs
        $role = $this->db->transaction(function () use ($data, $currentUser) {
            // Create the role
            $role = new $this->roleModel($data);
            $role->save();

            // Create activity record
            $this->userActivityLogger->info("User {$currentUser->user_name} created role {$role->name}.", [
                'type'    => 'role_create',
                'user_id' => $currentUser->id,
            ]);

            return $role;
        });

        return $role;
    }

    /**
     * Validate access to the page.
     *
     * @throws ForbiddenException
     */
    protected function validateAccess(): void
    {
        if (!$this->authenticator->checkAccess('create_role')) {
            throw new ForbiddenException();
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
