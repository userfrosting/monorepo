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
 * AuthGuard exception. Used when a page requires the user to be logged-in.
 */
final class AuthGuardException extends AccountException
{
    protected string $title = 'ACCOUNT.EXCEPTION.LOGIN_REQUIRED.TITLE';
    protected string|UserMessage $description = 'ACCOUNT.EXCEPTION.LOGIN_REQUIRED.DESCRIPTION';
    protected int $httpCode = 401; // Unauthorized
}
