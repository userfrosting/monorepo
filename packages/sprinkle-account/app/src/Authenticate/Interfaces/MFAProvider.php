<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate\Interfaces;

use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;

/**
 * Interface for user multi-factor authentication (MFA/2FA) providers.
 *
 * This interface facilitates the injection of the globally configured MFA
 * provider implementation into any class that requires the ability to
 * generate or validate a One-Time Password (OTP) or Time-based One-Time
 * Password (TOTP). It provides a standardized approach to handle multi-factor
 * authentication across various implementations.
 *
 * OTP/TOTP codes can be delivered through multiple methods, such as password
 * managers, email, or SMS. Providers that need to actually send the OTP to the
 * user via email or SMS, for instance, should implement this interface and
 * handle the delivery after generating the OTP in the `generate` method.
 *
 * Note: To specifically handle email verification, consider using the
 * `EmailVerificationProvider`.
 */
interface MFAProvider
{
    /**
     * Generate a new one time password or single use code for the given user.
     *
     * @param UserInterface $user    The user to create the OTP for.
     * @param int|null      $timeout The lifetime of the OTP in seconds, or null to use the provider default value.
     */
    public function generate(UserInterface $user, ?int $timeout = null): void;

    /**
     * Validate the given OTP code for the given user.
     *
     * @param UserInterface $user The user to validate the verification code for.
     * @param string|int    $code The verification code to validate.
     *
     * @return bool True if the verification code is valid, false otherwise.
     */
    public function validate(UserInterface $user, string|int $code): bool;
}
