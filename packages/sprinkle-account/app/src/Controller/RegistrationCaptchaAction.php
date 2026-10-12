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
use UserFrosting\Sprinkle\Account\Util\CaptchaProfiles;
use UserFrosting\Sprinkle\Core\Util\CaptchaFactoryInterface;
use UserFrosting\Sprinkle\Core\Util\CaptchaInterface;

/**
 * Generate a registration captcha and return the captcha image.
 *
 * Middleware: none
 * Route: /account/captcha
 * Route Name: account.captcha
 * Request type: GET
 */
class RegistrationCaptchaAction
{
    /**
     * Inject dependencies.
     */
    public function __construct(
        protected CaptchaFactoryInterface $captchaFactory,
    ) {
    }

    /**
     * Receive the request, dispatch to the handler, and return the payload to
     * the response.
     *
     * @param Request  $request
     * @param Response $response
     */
    public function __invoke(Request $request, Response $response): Response
    {
        $captcha = $this->handle();
        $response->getBody()->write($captcha->getImage());
        $response = $response->withStatus(200)->withHeader('Content-Type', 'image/png');

        return $captcha->withCookie($response);
    }

    /**
     * Handle the request and return the payload.
     *
     * @return CaptchaInterface
     */
    protected function handle(): CaptchaInterface
    {
        $captcha = $this->captchaFactory->forProfile(CaptchaProfiles::REGISTRATION);
        $captcha->generate();

        return $captcha;
    }
}
