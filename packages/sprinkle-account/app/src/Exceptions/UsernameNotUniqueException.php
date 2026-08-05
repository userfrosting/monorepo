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
 * Used when an the username is not unique.
 */
final class UsernameNotUniqueException extends AccountException
{
    protected string $title = 'USERNAME.INVALID';
    protected string $username = '';

    /**
     * {@inheritDoc}
     */
    public function getDescription(): UserMessage
    {
        return new UserMessage('USERNAME.IN_USE', ['user_name' => $this->username]);
    }

    /**
     * Set the value of username.
     *
     * @param string $username
     *
     * @return static
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }
}
