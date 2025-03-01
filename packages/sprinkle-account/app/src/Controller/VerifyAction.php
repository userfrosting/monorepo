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
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterVerificationEvent;
use UserFrosting\Sprinkle\Account\Repository\VerificationRepository;
use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;

/**
 * Processes an new email verification request.
 *
 * Processes the request from the email verification link that was emailed to the user, checking that:
 * 1. The token provided matches a user in the database;
 * 2. The user account is not already verified;
 * This route is "public access".
 *
 * Middleware: GuestGuard
 * Route: /account/verify
 * Route Name: account.login
 * Request type: GET
 */
class VerifyAction
{
    // Request schema to use to validate data.
    protected string $schema = 'schema://requests/account-verify.yaml';

    /**
     * Inject dependencies.
     *
     * @param \UserFrosting\Event\EventDispatcher $eventDispatcher
     */
    public function __construct(
        protected AlertStream $alert,
        protected EventDispatcherInterface $eventDispatcher,
        protected RouteParserInterface $routeParser,
        protected VerificationRepository $repoVerification,
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
        $event = $this->eventDispatcher->dispatch(new UserRedirectedAfterVerificationEvent());
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
        // GET parameters
        $params = $request->getQueryParams();

        // Load request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transformer->transform($schema, $params);

        // Validate, and halt on validation errors.
        // This is a GET request, so we redirect on validation error.
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

        // Process verification
        $verification = $this->repoVerification->complete($data['token']);

        if ($verification !== true) {
            // TODO : Remove dependency on AlertStream
            $this->alert->addMessage('danger', 'ACCOUNT.VERIFICATION.TOKEN_NOT_FOUND');

            return;
        }

        // TODO : Remove dependency on AlertStream
        $this->alert->addMessage('success', 'ACCOUNT.VERIFICATION.COMPLETE');
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
