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

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Exceptions\VerificationDisabledException;

/**
 * Handles a request from a guest user to send a verification code to the
 * specified email address to verify their email address. This route is publicly
 * accessible.
 *
 * Middleware: GuestGuard + NoCache
 * Route: /account/verify/request
 * Route Name: account.verify.request
 * Request type: POST
 */
class EmailVerificationRequestAction extends VerificationRequestAbstract
{
    protected function getSchemaName(): string
    {
        return 'schema://requests/resend-verification.yaml';
    }

    protected function getThrottlerSlug(): string
    {
        return 'account.verify.request';
    }

    protected function getMessage(): string
    {
        return 'ACCOUNT.VERIFICATION.CODE.SENT';
    }

    /**
     * Add verification the verification is enabled in the config.
     * {@inheritDoc}
     */
    public function __invoke(Request $request, Response $response): Response
    {
        // Make sure verification is enabled
        if (!$this->config->getBool('site.registration.require_email_verification', false)) {
            throw new VerificationDisabledException();
        }

        return parent::__invoke($request, $response);
    }

    /**
     * Make sure the user is not already verified.
     * {@inheritDoc}
     */
    protected function validateUser(UserInterface $user): bool
    {
        return $user->flag_verified === false;
    }
}
