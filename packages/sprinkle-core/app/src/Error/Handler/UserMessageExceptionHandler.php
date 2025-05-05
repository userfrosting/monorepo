<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Handler;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;

/**
 * Handles exceptions intended to be presented to the end user. Overrides the
 * default behavior and status code. Exceptions handled by this class are not
 * logged and do not display detailed information. They include a developer-
 * facing message and title, as well as a separate user-facing message. The
 * user-facing message is displayed to the end user either as an HTML page or
 * a JSON response.
 */
final class UserMessageExceptionHandler extends ExceptionHandler
{
    /**
     * Don't log theses exceptions.
     */
    protected function shouldLogExceptions(): bool
    {
        return false;
    }

    /**
     * Don't display details for theses exceptions.
     */
    protected function displayErrorDetails(): bool
    {
        return false;
    }

    /**
     * Force the use if Exception code for AuthException.
     */
    protected function determineStatusCode(ServerRequestInterface $request, Throwable $exception): int
    {
        return intval($exception->getCode());
    }
}
