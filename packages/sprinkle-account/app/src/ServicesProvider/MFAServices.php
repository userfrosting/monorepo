<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Authenticate\EmailOtpProvider;
use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\EmailVerificationProvider;
use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\MFAProvider;

/**
 * Provides services related to Multi-Factor Authentication (MFA/2FA).
 *
 * This class defines the MFA providers implementation used by the application
 * for various purposes, such as login, password changes, and email verification.
 *
 * The `MFAProvider` interface is responsible for generating and validating
 * One-Time Passwords (OTP) or Time-Based One-Time Passwords (TOTP) for users.
 * These codes can be delivered through various methods, such as password
 * managers, email, or SMS.
 *
 * The `EmailVerificationProvider` is specifically designed to handle the
 * generation and validation of OTP codes sent via email, ensuring the
 * verification of a user's email address.
 */
final class MFAServices implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            EmailVerificationProvider::class => \DI\autowire(EmailOtpProvider::class),
            MFAProvider::class               => \DI\autowire(EmailOtpProvider::class),
        ];
    }
}
