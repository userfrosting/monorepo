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
 * Used when an the a model property is not found.
 */
final class MissingRequiredParamException extends AccountException
{
    protected string $title = 'ACCOUNT.ERROR';
    protected string $param = '';

    /**
     * {@inheritDoc}
     */
    public function getDescription(): UserMessage
    {
        return new UserMessage('ACCOUNT.ERROR.MISSING_PARAM', ['param' => $this->param]);
    }

    /**
     * Set the value of param.
     *
     * @param string $param
     *
     * @return static
     */
    public function setParam(string $param): static
    {
        $this->param = $param;

        return $this;
    }
}
