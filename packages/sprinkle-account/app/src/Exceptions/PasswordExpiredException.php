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

use UserFrosting\Sprinkle\Account\Exceptions\Contracts\LoginException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Password expired exception.
 */
final class PasswordExpiredException extends AccountException implements LoginException
{
    protected string $title = 'ACCOUNT.EXCEPTION.PASSWORD_EXPIRED.TITLE';
    protected string|UserMessage $description = 'ACCOUNT.EXCEPTION.PASSWORD_EXPIRED.DESCRIPTION';
}
