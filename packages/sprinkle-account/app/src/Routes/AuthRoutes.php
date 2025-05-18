<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Routes;

use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use UserFrosting\Routes\RouteDefinitionInterface;
use UserFrosting\Sprinkle\Account\Authenticate\AuthGuard;
use UserFrosting\Sprinkle\Account\Authenticate\GuestGuard;
use UserFrosting\Sprinkle\Account\Controller\AuthCheckAction;
use UserFrosting\Sprinkle\Account\Controller\CaptchaAction;
use UserFrosting\Sprinkle\Account\Controller\CheckUsernameAction;
use UserFrosting\Sprinkle\Account\Controller\EmailVerificationRequestAction;
use UserFrosting\Sprinkle\Account\Controller\EmailVerificationValidationAction;
use UserFrosting\Sprinkle\Account\Controller\ForgetPasswordSetPasswordAction;
use UserFrosting\Sprinkle\Account\Controller\LoginAction;
use UserFrosting\Sprinkle\Account\Controller\LogoutAction;
use UserFrosting\Sprinkle\Account\Controller\ProfileEditAction;
use UserFrosting\Sprinkle\Account\Controller\ProfileEmailEditAction;
use UserFrosting\Sprinkle\Account\Controller\RegisterAction;
use UserFrosting\Sprinkle\Account\Controller\ResetPasswordRequestAction;
use UserFrosting\Sprinkle\Account\Controller\SettingsEditAction;
use UserFrosting\Sprinkle\Account\Controller\SuggestUsernameAction;
use UserFrosting\Sprinkle\Core\Middlewares\NoCache;

class AuthRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        // Guest Guard
        $app->group('/account', function (RouteCollectorProxy $group) {
            $group->post('/login', LoginAction::class)->setName('account.login');
            $group->post('/register', RegisterAction::class)->setName('account.register');
            $group->post('/forgot-password/request', ResetPasswordRequestAction::class)->setName('account.forgotPassword.request');
            $group->post('/forgot-password/set-password', ForgetPasswordSetPasswordAction::class)->setName('account.forgotPassword.setPassword');
            $group->post('/verify/email', EmailVerificationValidationAction::class)->setName('account.verify.email');
            $group->post('/verify/request', EmailVerificationRequestAction::class)->setName('account.verify.request');
        })->add(GuestGuard::class)->add(NoCache::class);

        // Auth Guard
        $app->group('/account', function (RouteCollectorProxy $group) {
            $group->get('/logout', LogoutAction::class)->setName('account.logout');
            $group->post('/settings', SettingsEditAction::class)->setName('settings');
            $group->post('/settings/profile', ProfileEditAction::class)->setName('settings.profile');
            $group->post('/settings/email', ProfileEmailEditAction::class)->setName('settings.email');
        })->add(AuthGuard::class)->add(NoCache::class);

        // No guard
        $app->group('/account', function (RouteCollectorProxy $group) {
            $group->get('/auth-check', AuthCheckAction::class)->setName('account.authCheck');
            $group->get('/captcha', CaptchaAction::class)->setName('account.captcha');
            $group->get('/check-username', CheckUsernameAction::class)->setName('account.checkUsername');
            $group->get('/suggest-username', SuggestUsernameAction::class)->setName('account.suggestUsername');
        })->add(NoCache::class);
    }
}
