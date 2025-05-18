<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Exceptions\Contracts;

/**
 * Interface for login-related exceptions.
 *
 * Exceptions implementing this interface indicate issues that occur during
 * the login process. These exceptions will be shown directly to the user,
 * rather than being rethrown or masked, to provide clear feedback about
 * authentication problems.
 */
interface LoginException
{
}
