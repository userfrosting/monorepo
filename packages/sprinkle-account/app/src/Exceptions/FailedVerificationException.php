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
 * Two-Factor Verification Exception. Used when a user submit an invalid token
 * for two-factor verification.
 */
final class FailedVerificationException extends AccountException
{
    protected string $title = 'ACCOUNT.EXCEPTION.VERIFICATION_FAILED.TITLE';
    protected string|UserMessage $description = 'ACCOUNT.EXCEPTION.VERIFICATION_FAILED.DESCRIPTION';
}
