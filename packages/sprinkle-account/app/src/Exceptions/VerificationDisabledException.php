<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Exceptions;

use UserFrosting\Support\Message\UserMessage;

/**
 * Used when a user tries to verify their account but the verification is disabled.
 */
final class VerificationDisabledException extends AccountException
{
    protected string $title = 'ACCOUNT.EXCEPTION.VERIFICATION_DISABLED.TITLE';
    protected string|UserMessage $description = 'ACCOUNT.EXCEPTION.VERIFICATION_DISABLED.DESCRIPTION';
}
