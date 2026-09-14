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
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\RoleInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorderInterface;
use UserFrosting\Sprinkle\Admin\Exceptions\MissingRequiredParamException;
use UserFrosting\Sprinkle\Admin\Log\RoleActivityTypes;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;
use UserFrosting\Support\Message\UserMessage;

/**
 * Assigns permissions to an existing role.
 */
class RolePermissionsAction
{
    // Request schema for client side form validation
    protected string $schema = 'schema://requests/role/permissions.yaml';

    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected Cache $cache,
        protected Connection $db,
        protected ActivityRecorderInterface $logger,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param RoleInterface $role     The role to update, injected by middleware.
     * @param Request       $request
     * @param Response      $response
     */
    public function __invoke(
        RoleInterface $role,
        Request $request,
        Response $response
    ): Response {
        $message = $this->handle($role, $request);
        $message = $this->translator->translate($message->message, $message->parameters);
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
     * @return UserMessage The message to display to the user.
     */
    protected function handle(
        RoleInterface $role,
        Request $request
    ): UserMessage {
        // Access-control - check that current User has permission, and get the
        // current user
        $currentUser = $this->authorize();

        // Get PUT parameters: value
        $put = (array) $request->getParsedBody();

        // Make sure data is part of $_PUT data.
        if (!array_key_exists('permissions', $put)) {
            $e = new MissingRequiredParamException();
            $e->setParam('permissions');

            throw $e;
        }

        // Load the request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transformer->transform($schema, $put);

        // Validate request data
        $this->validateData($schema, $data);

        $permissionIds = $data['permissions'];

        $this->db->transaction(function () use ($role, $currentUser, $permissionIds): void {
            // Prepare data for the activity record
            $oldPermissions = $role->permissions()->get()->pluck('name', 'id')->all();
            $newPermissions = Permission::query()->whereKey($permissionIds)->pluck('name', 'id')->all();
            $addedPermissions = implode(', ', array_values(array_diff_key($newPermissions, $oldPermissions)));
            $removedPermissions = implode(', ', array_values(array_diff_key($oldPermissions, $newPermissions)));

            // Change data in the database
            $role->permissions()->sync($permissionIds);

            // All user's permissions are cached. Clear cache.
            $this->cache->clear();

            $this->logger->record(
                user: $currentUser,
                type: RoleActivityTypes::UPDATE_PERMISSIONS,
                subject: $role,
                metadata: [
                    'added_permissions'   => $addedPermissions !== '' ? $addedPermissions : $this->translator->translate('PERMISSION.NONE'),
                    'removed_permissions' => $removedPermissions !== '' ? $removedPermissions : $this->translator->translate('PERMISSION.NONE'),
                ],
            );
        });

        return new UserMessage('ROLE.PERMISSIONS_UPDATED', ['name' => $role->name]);
    }

    /**
     * Authorize the user.
     *
     * @return UserInterface
     */
    protected function authorize(): UserInterface
    {
        if (!$this->authenticator->checkAccess('update_role_field')) {
            throw new ForbiddenException();
        }

        /** @var UserInterface */
        return $this->authenticator->user();
    }

    /**
     * Load the request schema.
     *
     * @return RequestSchemaInterface
     */
    protected function getSchema(): RequestSchemaInterface
    {
        return new RequestSchema($this->schema);
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
