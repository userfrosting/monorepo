<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate;

use Slim\Views\Twig;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Account\Authenticate\Interfaces\EmailVerificationProvider;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserVerificationInterface;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;

/**
 * Provides One-Time Password (OTP) and email verification functionality.
 * This class sends a 6-digit code, stored in the database, for authentication
 * and email verification purposes.
 */
class EmailOtpProvider extends SendableOtpProvider implements EmailVerificationProvider
{
    /**
     * @var string The Twig template to use for the verification email
     */
    protected string $template = 'mail/code.html.twig';

    /**
     * Inject Dependencies.
     *
     * @param UserVerificationInterface $model  The model to use to store verification codes
     * @param Config                    $config The config service
     * @param Twig                      $twig   The Twig service
     * @param Mailer                    $mailer The mailer service
     */
    public function __construct(
        protected UserVerificationInterface $model,
        protected Config $config,
        protected Twig $twig,
        protected Mailer $mailer,
    ) {
        parent::__construct($model, $config);
    }

    /**
     * {@inheritDoc}
     */
    protected function sendCode(UserInterface $user, string $code): void
    {
        // Create and send verification email
        $message = new TwigMailMessage($this->twig, $this->template);

        // @phpstan-ignore-next-line Config limitation
        $message->from($this->config->get('address_book.admin'))
                ->addEmailRecipient(new EmailRecipient($user->email, $user->full_name))
                ->addParams([
                    'user' => $user,
                    'code' => $code,
                ]);

        $this->mailer->send($message);
    }
}
