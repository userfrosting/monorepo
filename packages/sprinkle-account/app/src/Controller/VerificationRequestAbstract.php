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
use Illuminate\Database\Eloquent\Model;
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
use UserFrosting\Sprinkle\Core\Exceptions\ValidationException;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottlerDelayException;
use UserFrosting\Sprinkle\Core\Util\ApiResponse;

/**
 * Abstract class for handling verification requests. Can be used to send
 * verification codes.
 *
 * This action enforces the following checks:
 * 1. Ensures the rate limit for this type of request is respected;
 * 2. Verifies that the provided email is linked to an existing user account;
 * 3. Confirms the user account is not already verified;
 * 4. Validates the submitted data against the defined schema.
 */
abstract class VerificationRequestAbstract
{
    /**
     * Inject dependencies.
     *
     * @param Translator                $translator
     * @param Config                    $config
     * @param Connection                $db
     * @param Throttler                 $throttler
     * @param UserInterface             $userModel
     * @param EmailVerificationProvider $emailVerification
     * @param RequestDataTransformer    $transformer
     * @param ServerSideValidator       $validator
     */
    public function __construct(
        protected Translator $translator,
        protected Config $config,
        protected Connection $db,
        protected Throttler $throttler,
        protected UserInterface $userModel,
        protected EmailVerificationProvider $emailVerification,
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
        $message = $this->handle($request);
        $payload = new ApiResponse($message);
        $response->getBody()->write((string) $payload);

        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Handle the request and return the payload.
     *
     * @param Request $request
     *
     * @return string The message to return to the frontend
     */
    protected function handle(Request $request): string
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
            $this->throttler->logEvent($this->getThrottlerSlug(), [
                'email' => $data['email'],
            ]);

            // Load the user, by email address
            /** @var (UserInterface&Model)|null $user */
            $user = $this->userModel->firstWhere('email', $data['email']);

            // Verify that the user exists and is not already verified.
            // If no user is found with the provided email, or if the user
            // exists but is already verified, we act as if the operation
            // succeeded. This prevents potential account enumeration attacks.
            if ($user !== null && $this->validateUser($user) === true) {
                $this->emailVerification->generate($user);
            }
        });

        return $this->translator->translate($this->getMessage(), $data);
    }

    /**
     * This method should return true if the user is valid for the
     * verification request. Return false if the user is not valid.
     * Extend this method in the child class to add custom validation.
     *
     * @param UserInterface $user
     *
     * @return bool
     */
    protected function validateUser(UserInterface $user): bool
    {
        return true;
    }

    /**
     * Load the request schema.
     *
     * @return RequestSchemaInterface
     */
    private function getSchema(): RequestSchemaInterface
    {
        return new RequestSchema($this->getSchemaName());
    }

    /**
     * Validate request POST data.
     *
     * @param RequestSchemaInterface $schema
     * @param mixed[]                $data
     *
     * @throws ValidationException If the data is invalid
     */
    private function validateData(RequestSchemaInterface $schema, array $data): void
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
    private function throttle(string $email): void
    {
        $delay = $this->throttler->getDelay($this->getThrottlerSlug(), [
            'email' => $email,
        ]);
        if ($delay > 0) {
            $e = new ThrottlerDelayException();
            $e->setDelay($delay);

            throw $e;
        }
    }

    /**
     * Get the request schema to use to validate data.
     *
     * @return string
     */
    abstract protected function getSchemaName(): string;

    /**
     * Get the throttler key slug.
     *
     * @return string
     */
    abstract protected function getThrottlerSlug(): string;

    /**
     * Get the message to return to the frontend.
     *
     * @return string
     */
    abstract protected function getMessage(): string;
}
