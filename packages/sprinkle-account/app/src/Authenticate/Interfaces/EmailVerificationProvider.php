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

/**
 * Interface for user email verification providers.
 *
 * This interface extends the MFAProvider interface but is specifically
 * designed for email verification. Providers implementing this interface
 * must handle the generation and validation of OTP codes specifically for
 * email verification. This means they are REQUIRED to send the OTP to the
 * user via email.
 *
 * Note an EmailVerificationProvider can be used as a MFAProvider, but not the
 * other way around.
 */
interface EmailVerificationProvider extends MFAProvider
{
}
