<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Throttle;

use Exception;
use UserFrosting\Sprinkle\Core\Exceptions\UserFacingException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Exception thrown when the throttler delay.
 */
final class ThrottlerDelayException extends UserFacingException
{
    protected string $title = 'ERROR.RATE_LIMIT_EXCEEDED.TITLE';
    protected string|UserMessage $description = 'ERROR.RATE_LIMIT_EXCEEDED.DESCRIPTION';
    protected string $twigTemplate = 'pages/error/throttler.html.twig';
    protected int $httpCode = 429;

    /**
     * @var int The delay before the user can re-attempt
     */
    protected int $delay = 0;

    /**
     * {@inheritDoc}
     */
    public function getDescription(): UserMessage
    {
        // @phpstan-ignore-next-line - Property is a string
        return new UserMessage($this->description, ['delay' => $this->delay]);
    }

    /**
     * Set delay before the user can re-attempt.
     *
     * @param int $delay
     */
    public function setDelay(int $delay): void
    {
        $this->delay = $delay;
    }
}
