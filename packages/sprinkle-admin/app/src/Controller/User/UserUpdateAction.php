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
use UserFrosting\Sprinkle\Account\Exceptions\ForbiddenException;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;
use UserFrosting\Support\Message\UserMessage;

/**
 * Shared behavior for focused administrative user update actions.
 */
abstract class UserUpdateAction
{
    protected string $schema;

    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected Config $config,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator,
    ) {
    }

    /**
     * Check access to a user update and return the authenticated actor.
     */
    protected function authorize(UserInterface $user, string $permission): UserInterface
    {
        if (!$this->authenticator->checkAccess($permission)) {
            throw new ForbiddenException();
        }

        /** @var UserInterface */
        $currentUser = $this->authenticator->user();

        if (
            $user->id === $this->config->getInt('reserved_user_ids.master') &&
            $currentUser->id !== $this->config->getInt('reserved_user_ids.master')
        ) {
            throw new ForbiddenException();
        }

        return $currentUser;
    }

    /**
     * Transform and validate a request against its dedicated schema.
     *
     * @return mixed[]
     */
    protected function transform(RequestSchemaInterface $schema, Request $request): array
    {
        $data = $this->transformer->transform($schema, (array) $request->getParsedBody());
        $errors = $this->validator->validate($schema, $data);

        if (count($errors) !== 0) {
            $e = new ValidationException();
            $e->addErrors($errors);

            throw $e;
        }

        return $data;
    }

    /**
     * Load the request schema for this update action.
     */
    protected function getSchema(): RequestSchemaInterface
    {
        return new RequestSchema($this->schema);
    }

    /**
     * Create the standard API response used by user update actions.
     */
    protected function respond(Response $response, UserMessage $message): Response
    {
        $message = $this->translator->translate($message->message, $message->parameters);
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
    }
}
