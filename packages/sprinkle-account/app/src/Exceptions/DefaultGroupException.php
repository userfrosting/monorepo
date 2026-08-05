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
 * Default group exception. Used when the default group is not found.
 */
final class DefaultGroupException extends AccountException
{
    protected string $title = 'ACCOUNT.EXCEPTION.DEFAULT_GROUP.TITLE';
    protected string $slug = '';

    /**
     * {@inheritDoc}
     */
    public function getDescription(): UserMessage
    {
        return new UserMessage('ACCOUNT.EXCEPTION.DEFAULT_GROUP.DESCRIPTION', ['slug' => $this->slug]);
    }

    /**
     * Set the value of slug.
     *
     * @param string $slug
     *
     * @return static
     */
    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
