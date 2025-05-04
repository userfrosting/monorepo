<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Controller;

use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Handles a request from a guest user to send a verification code to the
 * specified email address to reset their forgotten password. This route is
 * publicly accessible.
 *
 * Middleware: GuestGuard
 * Route: /account/forgot-password/request
 * Route Name: account.forgotPassword.request
 * Request type: POST
 */
class ForgetPasswordRequestAction extends VerificationRequestAbstract
{
    protected function getSchemaName(): string
    {
        return 'schema://requests/forgot-password.yaml';
    }

    protected function getThrottlerSlug(): string
    {
        return 'account.password.reset.request';
    }

    protected function getMessage(): string
    {
        return 'PASSWORD.FORGOT.REQUEST_SENT';
    }
}
