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
 * Unverified account exception. Used when an account is required to complete email verification, but hasn't done so yet.
 */
final class AccountNotVerifiedException extends AccountException implements LoginException
{
    protected string $title = 'ACCOUNT.EXCEPTION.UNVERIFIED.TITLE';
    protected string|UserMessage $description = 'ACCOUNT.EXCEPTION.UNVERIFIED.DESCRIPTION';
}
