<?php

declare(strict_types=1);

/*
 * UserFrosting Admin Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-admin
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-admin/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Exceptions;

use UserFrosting\Sprinkle\Core\Exceptions\UserFacingException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Used when an the a model property is not found.
 */
final class MissingRequiredParamException extends UserFacingException
{
    protected string $title = 'VALIDATE.ERROR';
    protected string $param = '';

    /**
     * {@inheritDoc}
     */
    public function getDescription(): UserMessage
    {
        return new UserMessage('VALIDATE.REQUIRED', ['label' => $this->param]);
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
