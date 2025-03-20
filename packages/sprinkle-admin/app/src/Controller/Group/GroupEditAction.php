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
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\GroupInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Admin\Exceptions\GroupException;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Processes the request to update an existing group's details.
 *
 * Processes the request from the group update form, checking that:
 * 1. The group name/slug are not already in use;
 * 2. The user has the necessary permissions to update the posted field(s);
 * 3. The submitted data is valid.
 * This route requires authentication (and should generally be limited to admins or the root user).
 *
 * Request type: PUT
 */
class GroupEditAction
{
    // Request schema for client side form validation
    protected string $schema = 'schema://requests/group/edit-info.yaml';

    /**
     * Inject dependencies.
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected Config $config,
        protected Connection $db,
        protected UserActivityLogger $userActivityLogger,
        protected GroupInterface $groupModel,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param GroupInterface $group    The group to update, injected from middleware.
     * @param Request        $request
     * @param Response       $response
     */
    public function __invoke(GroupInterface $group, Request $request, Response $response): Response
    {
        $group = $this->handle($group, $request);
        $payload = json_encode([
            'success' => true,
            'message' => $this->translator->translate('GROUP.UPDATE', $group->toArray()),
            'group'   => $group,
        ], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request.
     *
     * @param GroupInterface $group
     * @param Request        $request
     *
     * @return GroupInterface
     */
    protected function handle(GroupInterface $group, Request $request): GroupInterface
    {
        // Get PUT parameters
        $params = (array) $request->getParsedBody();

        // Load the request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transformer->transform($schema, $params);

        // Validate request data
        $this->validateData($schema, $data);
        if ($data['name'] !== $group->name) {
            $this->validateGroupName($data['name']);
        }
        if ($data['slug'] !== $group->slug) {
            $this->validateGroupSlug($data['slug']);
        }

        // Determine targeted fields
        $fieldNames = [];
        foreach ($data as $name => $value) {
            $fieldNames[] = $name;
        }

        // Access-controlled resource - check that currentUser has permission to edit submitted fields for this user
        if (!$this->authenticator->checkAccess('update_group_field')) {
            throw new ForbiddenException();
        }

        // Get current user. Won't be null, as AuthGuard prevent it
        /** @var UserInterface */
        $currentUser = $this->authenticator->user();

        // Begin transaction - DB will be rolled back if an exception occurs
        $this->db->transaction(function () use ($data, $group, $currentUser) {
            // Update the user and generate success messages
            foreach ($data as $name => $value) {
                $group->setAttribute($name, $value);
            }

            $group->save();

            // Create activity record
            $this->userActivityLogger->info("User {$currentUser->user_name} updated details for group {$group->name}.", [
                'type'    => 'group_update_info',
                'user_id' => $currentUser->id,
            ]);
        });

        return $group;
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
     * Validate group name is not already in use.
     *
     * @param string $name
     */
    protected function validateGroupName(string $name): void
    {
        $group = $this->groupModel->where('name', $name)->first();
        if ($group !== null) {
            $e = new GroupException();
            $message = new UserMessage('GROUP.NAME_IN_USE', ['name' => $name]);
            $e->setDescription($message);

            throw $e;
        }
    }

    /**
     * Validate group name is not already in use.
     *
     * @param string $slug
     */
    protected function validateGroupSlug(string $slug): void
    {
        $group = $this->groupModel->where('slug', $slug)->first();
        if ($group !== null) {
            $e = new GroupException();
            $message = new UserMessage('SLUG_IN_USE', ['slug' => $slug]);
            $e->setDescription($message);

            throw $e;
        }
    }
}
