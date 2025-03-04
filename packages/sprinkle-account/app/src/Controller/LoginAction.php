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
use UserFrosting\Config\Config;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Event\UserRedirectedAfterLoginEvent;
use UserFrosting\Sprinkle\Account\Exceptions\AccountException;
use UserFrosting\Sprinkle\Account\Exceptions\InvalidCredentialsException;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottlerDelayException;

/**
 * Processes an account login request.
 *
 * Processes the request from the form on the login page, checking that:
 * 1. The user is not already logged in.
 * 2. The rate limit for this type of request is being observed.
 * 3. Email login is enabled, if an email address was used.
 * 4. The user account exists.
 * 5. The user account is enabled and verified.
 * 6. The user entered a valid username/email and password.
 * This route, by definition, is "public access".
 *
 * Middleware: GuestGuard
 * Route: /account/login
 * Route Name: account.login
 * Request type: POST
 */
class LoginAction
{
    // Request schema to use to validate data.
    protected string $schema = 'schema://requests/login.yaml';

    /**
     * Inject dependencies.
     *
     * @param \UserFrosting\Event\EventDispatcher $eventDispatcher
     */
    public function __construct(
        protected Translator $translator,
        protected Authenticator $authenticator,
        protected Config $config,
        protected EventDispatcherInterface $eventDispatcher,
        protected Throttler $throttler,
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
        $user = $this->handle($request);
        $response = $this->writeResponse($response, $user);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Write to the response object.
     *
     * @param Response      $response
     * @param UserInterface $user
     *
     * @return Response
     */
    protected function writeResponse(Response $response, UserInterface $user): Response
    {
        // Get redirect target and add Header
        $event = $this->eventDispatcher->dispatch(new UserRedirectedAfterLoginEvent());
        if ($event->getRedirect() !== null) {
            // TODO : Header should be deprecated, see dilemma between overall redirect vs Vue Router redirect
            $response = $response->withHeader('UF-Redirect', $event->getRedirect());
        }

        // Define payload
        $data = [
            'user'        => $user->attributesToArray(),
            'permissions' => $user->permissions->pluck('conditions', 'slug'),
            'message'     => $this->translator->translate('WELCOME', $user->toArray()),
            'redirect'    => $event->getRedirect() ?? '',
        ];

        // Write response with the user info in it
        $payload = json_encode($data, JSON_THROW_ON_ERROR);
        $response->getBody()->write($payload);

        return $response;
    }

    /**
     * Handle the request and return the payload.
     *
     * @param Request $request
     *
     * @return UserInterface
     */
    protected function handle(Request $request): UserInterface
    {
        // Get POST parameters
        $params = (array) $request->getParsedBody();

        // Load the request schema
        $schema = $this->getSchema();

        // Whitelist and set parameter defaults
        $data = $this->transformer->transform($schema, $params);

        // Validate request data, and halt on validation errors.
        // Failed validation attempts do not count towards throttling limit.
        $this->validateData($schema, $data);

        // Determine whether we are trying to log in with an email address or a username
        $userIdentifier = $data['user_name'];
        $isEmail = filter_var($userIdentifier, FILTER_VALIDATE_EMAIL);

        // Throttle requests.
        $this->throttle($userIdentifier);

        // If credential is an email address, but email login is not enabled, raise an error.
        // Note that this error counts towards the throttling limit.
        if ($isEmail == true && $this->config->get('site.login.enable_email') === false) {
            $this->throttler->logEvent('sign_in_attempt', [
                'user_identifier' => $userIdentifier,
            ]);

            // Throw exception
            throw new InvalidCredentialsException();
        }

        // Try to authenticate the user.  Authenticator will throw an exception on failure.
        try {
            $currentUser = $this->authenticator->attempt($isEmail == true ? 'email' : 'user_name', $userIdentifier, $data['password'], $data['rememberme'] == true);
        } catch (AccountException $e) {
            // only let unsuccessful logins count toward the throttling limit
            $this->throttler->logEvent('sign_in_attempt', [
                'user_identifier' => $userIdentifier,
            ]);

            // Rethrow as InvalidCredentialsException not give away the actual
            // exception to the end user for security.
            throw new InvalidCredentialsException();
        }

        return $currentUser;
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

    /**
     * Throttle requests.
     */
    protected function throttle(string $userIdentifier): void
    {
        $delay = $this->throttler->getDelay('sign_in_attempt', [
            'user_identifier' => $userIdentifier,
        ]);
        if ($delay > 0) {
            $e = new ThrottlerDelayException();
            $e->setDelay($delay);

            throw $e;
        }
    }
}
