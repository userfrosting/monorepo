<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate;

use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\MFAProvider;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserVerificationInterface;

/**
 * Abstract class for One-Time Password (OTP) providers. This class uses a
 * sendable code (e.g., email, SMS) that is stored in the database for
 * authentication purposes.
 */
abstract class SendableOtpProvider implements MFAProvider
{
    /**
     * Inject Dependencies.
     *
     * @param UserVerificationInterface $model  The model to use to store verification codes
     * @param Config                    $config The config service
     */
    public function __construct(
        protected UserVerificationInterface $model,
        protected Config $config,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function generate(UserInterface $user, ?int $timeout = null): void
    {
        // Remove any expired or existing verifications for this user
        $this->model->forUser($user)->delete();
        $this->model->clearExpired();

        // Generate a new code
        $code = $this->generateCode();

        // Set default timeout if not provided
        $timeout = $timeout ?? $this->config->getInt('otp.timeout', 600);

        // Create a new token record
        $this->model->storeCode($user, $code, $timeout);

        // Send the code to the user
        $this->sendCode($user, $code, $timeout);
    }

    /**
     * {@inheritDoc}
     */
    public function validate(UserInterface $user, string|int $code): bool
    {
        return $this->model->validateCode($user, $code);
    }

    /**
     * Generate a new random code.
     *
     * @return string
     */
    protected function generateCode(): string
    {
        return (string) mt_rand(100000, 999999);
    }

    /**
     * Send the generated code to the user.
     *
     * @param UserInterface $user
     * @param string        $code
     * @param int           $timeout The timeout for the code, in seconds
     */
    abstract protected function sendCode(UserInterface $user, string $code, int $timeout): void;
}
