<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Controller;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Config\Config;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterDenyResetPasswordEvent;
use UserFrosting\Sprinkle\Account\Repository\PasswordResetRepository;
use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;

/**
 * Processes a request to cancel a password reset request.
 *
 * This is provided so that users can cancel a password reset request, if they made it in error or if it was not initiated by themselves.
 * Processes the request from the password reset link, checking that:
 * 1. The provided token is associated with an existing user account, who has a pending password reset request.
 *
 * Middleware: GuestGuard
 * Route: /account/set-password/deny
 * Route Name: account.setPassword.deny
 * Request type: GET
 */
class DenyResetPasswordAction
{
    // Request schema to use to validate data.
    protected string $schema = 'schema://requests/deny-password.yaml';

    /**
     * Inject dependencies.
     *
     * @param \UserFrosting\Event\EventDispatcher $eventDispatcher
     */
    public function __construct(
        protected AlertStream $alert,
        protected EventDispatcherInterface $eventDispatcher,
        protected Config $config,
        protected RouteParserInterface $routeParser,
        protected PasswordResetRepository $repoPasswordReset,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator
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
        $this->handle($request);

        // Get redirect target and add Header
        $event = $this->eventDispatcher->dispatch(new UserRedirectedAfterDenyResetPasswordEvent());
        if ($event->getRedirect() !== null) {
            return $response
                ->withHeader('Location', $event->getRedirect())
                ->withStatus(302);
        }

        // Write empty response
        $payload = json_encode([], JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request and return the payload.
     *
     * @param Request $request
     */
    protected function handle(Request $request): void
    {
        // Get GET parameters
        $params = $request->getQueryParams();

        // Load the request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transformer->transform($schema, $params);

        // Validate request data
        $errors = $this->validator->validate($schema, $data);
        if (count($errors) !== 0) {
            foreach ($errors as $idx => $field) {
                foreach ($field as $eidx => $error) {
                    // TODO : Remove dependency on AlertStream
                    $this->alert->addMessage('danger', $error);
                }
            }

            return;
        }

        // Cancel repository
        $passwordReset = $this->repoPasswordReset->cancel($data['token']);
        if ($passwordReset === false) {
            // TODO : Remove dependency on AlertStream
            $this->alert->addMessage('danger', 'ACCOUNT.EXCEPTION.PASSWORD_RESET.TITLE');

            return;
        }

        // TODO : Remove dependency on AlertStream
        $this->alert->addMessage('success', 'PASSWORD.FORGET.REQUEST_CANNED');
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
}
