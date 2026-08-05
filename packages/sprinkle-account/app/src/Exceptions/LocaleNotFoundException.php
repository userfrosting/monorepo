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
 * Expired authentication exception. Used when the user needs to authenticate/reauthenticate.
 */
final class LocaleNotFoundException extends AccountException
{
    protected string $locale = '';

    /**
     * {@inheritDoc}
     */
    public function getDescription(): UserMessage
    {
        return new UserMessage('LOCALE.INVALID', ['locale' => $this->locale]);
    }

    /**
     * Set the value of locale.
     *
     * @param string $locale
     *
     * @return static
     */
    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }
}
