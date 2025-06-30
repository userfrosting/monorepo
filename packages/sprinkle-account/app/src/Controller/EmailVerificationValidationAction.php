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
use Psr\EventDispatcher\EventDispatcherInterface;
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
use UserFrosting\Sprinkle\Account\Exceptions\FailedVerificationException;
use UserFrosting\Sprinkle\Account\Exceptions\VerificationDisabledException;
use UserFrosting\Sprinkle\Account\Log\UserActivityLoggerInterface;
use UserFrosting\Sprinkle\Account\Log\UserActivityTypes;
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottlerDelayException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;

/**
 * Handles a request from a guest user to verify an email using a verification
 * code sent to the specified email address. This route is publicly accessible.
 *
 * This action enforces the following checks:
 * 1. Ensures the rate limit for this type of request is respected.
 * 2. Verifies that the provided email is linked to an existing user account.
 * 3. Confirms the user account is not already verified.
 * 4. Validates the submitted data against the defined schema.
 * 5. Validates the verification code against the Email Verification Provider.
 *
 * Middleware: GuestGuard + NoCache
 * Route: /account/verify/email
 * Route Name: account.verify.email
 * Request type: POST
 */
class EmailVerificationValidationAction
{
    /**
     * @var string Request schema to use to validate data.
     */
    protected string $schema = 'schema://requests/account-verify.yaml';

    /**
     * @var string Throttler key slug. This slug is distinct from the one
     *             used for requesting a verification code, ensuring that the two
     *             actions do not interfere with each other's throttling limits.
     */
    protected string $throttlerSlug = 'account.verify.email';

    /**
     * Inject dependencies.
     *
     * @param Translator                          $translator
     * @param \UserFrosting\Event\EventDispatcher $eventDispatcher
     * @param EmailVerificationProvider           $emailVerification
     * @param RequestDataTransformer              $transformer
     * @param ServerSideValidator                 $validator
     * @param UserInterface                       $userModel
     * @param UserActivityLoggerInterface         $logger
     * @param Connection                          $db
     * @param Throttler                           $throttler
     */
    public function __construct(
        protected Translator $translator,
        protected EventDispatcherInterface $eventDispatcher,
        protected EmailVerificationProvider $emailVerification,
        protected RequestDataTransformer $transformer,
        protected ServerSideValidator $validator,
        protected UserInterface $userModel,
        protected UserActivityLoggerInterface $logger,
        protected Connection $db,
        protected Throttler $throttler,
        protected Config $config,
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
        // Make sure verification is enabled
        if (!$this->config->getBool('site.registration.require_email_verification', false)) {
            throw new VerificationDisabledException();
        }

        // Handle the request and perform the verification
        $this->handle($request);

        // Write response
        $message = $this->translator->translate('ACCOUNT.VERIFICATION.COMPLETE');
        $payload = new ApiResponse($message);
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
        $this->throttle($data['email']);

        // Basic checks passed. Begin transaction - DB will be rolled back if
        // an exception occurs.
        $this->db->transaction(function () use ($data) {
            // Log throttle-able event
            $this->throttler->logEvent($this->throttlerSlug, [
                'email' => $data['email'],
            ]);

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
            if ($user === null ||
                $user->flag_verified ||
                !$this->emailVerification->validate($user, $data['code'])) {
                throw new FailedVerificationException();
            }

            // Verification was successful, update the user account.
            $user->flag_verified = true;
            $user->save();

            // Create activity record
            $this->logger->info("User {$user->user_name} verified it's account.", [
                'type'    => UserActivityTypes::VERIFIED,
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
        return new RequestSchema($this->schema);
    }

    /**
     * Validate request POST data.
     *
     * @param RequestSchemaInterface $schema
     * @param mixed[]                $data
     *
     * @throws ValidationException If the data is invalid
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
     * Enforce rate limiting for requests to prevent abuse.
     *
     * @param string $email
     *
     * @throws ThrottlerDelayException If the throttle limit is reached
     */
    protected function throttle(string $email): void
    {
        $delay = $this->throttler->getDelay($this->throttlerSlug, [
            'email' => $email,
        ]);
        if ($delay > 0) {
            $e = new ThrottlerDelayException();
            $e->setDelay($delay);

            throw $e;
        }
    }
}
