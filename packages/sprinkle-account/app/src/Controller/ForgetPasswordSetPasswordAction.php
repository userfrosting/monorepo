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

use Illuminate\Database\Connection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\Fortress\RequestSchema;
use UserFrosting\Fortress\RequestSchema\RequestSchemaInterface;
use UserFrosting\Fortress\Transformer\RequestDataTransformer;
use UserFrosting\Fortress\Validator\ServerSideValidator;
use UserFrosting\I18n\Translator;
use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\EmailVerificationProvider;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\PasswordResetInvalidException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLoggerInterface;
use UserFrosting\Sprinkle\Account\Log\UserActivityTypes;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;
use UserFrosting\Sprinkle\Core\Util\RouteParserInterface;

/**
 * Processes a request to set the password for a new or current user.
 *
 * Processes the request from the password create/reset form, which should have the secret token embedded in it, checking that:
 * 1. The provided secret token is associated with an existing user account;
 * 2. The user has a password set/reset request in progress;
 * 3. The token has not expired;
 * 4. The submitted data (new password) is valid.
 * This route is "public access".
 *
 * Middleware: GuestGuard
 * Route: /account/set-password
 * Route Name: account.setPassword
 * Request type: POST
 */
class ForgetPasswordSetPasswordAction
{
    /**
     * @var string Request schema to use to validate data.
     */
    protected string $schema = 'schema://requests/set-password.yaml';

    /**
     * Inject dependencies.
     *
     * @param Translator                  $translator
     * @param Config                      $config
     * @param RouteParserInterface        $routeParser
     * @param EmailVerificationProvider   $emailVerification
     * @param RequestDataTransformer      $transformer
     * @param ServerSideValidator         $validator
     * @param Connection                  $db
     * @param UserInterface               $userModel
     * @param UserActivityLoggerInterface $logger
     */
    public function __construct(
        protected Translator $translator,
        protected Config $config,
        protected RouteParserInterface $routeParser,
        protected EmailVerificationProvider $emailVerification,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator,
        protected Connection $db,
        protected UserInterface $userModel,
        protected UserActivityLoggerInterface $logger,
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
        $payload = new ApiResponse($this->translator->translate('PASSWORD.UPDATED'));
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request and return the payload.
     *
     * @param Request $request
     */
    protected function handle(Request $request): void
    {
        // Get POST parameters
        $params = (array) $request->getParsedBody();

        // Load the request schema, apply parameter defaults, whitelist fields,
        // and validate the request data. Throttle requests to prevent abuse.
        $schema = $this->getSchema();
        $data = $this->transformer->transform($schema, $params);
        $this->validateData($schema, $data);

        // Basic checks passed. Begin transaction - DB will be rolled back if
        // an exception occurs.
        $this->db->transaction(function () use ($data) {
            // Load the user, by email address
            /** @var UserInterface|null */
            $user = $this->userModel->firstWhere('email', $data['email']);

            // Verify that the user exists and is not already verified.
            // If no user is found with the provided email, or if the user
            // exists but is already verified, we act as if the token
            // was invalid. This way, we don't leak information about whether
            // the email address exists in the system or not, or whether
            // the user is already verified. This is a security measure to
            // prevent account enumeration attacks.
            if ($user === null || !$this->emailVerification->validate($user, $data['code'])) {
                throw new PasswordResetInvalidException();
            }

            // Verification was successful, update the user account.
            $user->setPasswordAttribute($data['password']);
            $user->save();

            // Create activity record
            $this->logger->info("User {$user->user_name} reset it's password.", [
                'type'    => UserActivityTypes::PASSWORD_RESET,
                'user_id' => $user->id,
            ]);
        });
    }

    /**
     * Load the request schema.
     *
     * @return RequestSchemaInterface
     */
    protected function getSchema(): RequestSchemaInterface
    {
        $schema = new RequestSchema($this->schema);
        $schema->set('password.validators.length.min', $this->config->get('site.password.length.min'));
        $schema->set('password.validators.length.max', $this->config->get('site.password.length.max'));
        $schema->set('passwordc.validators.length.min', $this->config->get('site.password.length.min'));
        $schema->set('passwordc.validators.length.max', $this->config->get('site.password.length.max'));

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
