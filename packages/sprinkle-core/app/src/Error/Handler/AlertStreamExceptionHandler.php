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

use DI\Attribute\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UserFrosting\Alert\AlertStream;
use UserFrosting\Sprinkle\Core\Exceptions\Contracts\UserMessageException;
use UserFrosting\Support\Message\UserMessage;

/**
 * Handles exceptions intended to be shown to the end user via the Alert Stream
 * service. Overrides the default behavior and status code.
 */
final class AlertStreamExceptionHandler extends ExceptionHandler
{
    #[Inject]
    protected AlertStream $alert;

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

    /**
     * {@inheritdoc}
     *
     * Adds the exception message to the alert stream.
     */
    public function handle(ServerRequestInterface $request, Throwable $exception): ResponseInterface
    {
        if ($exception instanceof UserMessageException) {
            $title = $this->translateExceptionPart($exception->getTitle());
            $description = $this->translateExceptionPart($exception->getDescription());
            $this->alert->addMessage('danger', "$title: $description");
        }

        return parent::handle($request, $exception);
    }

    /**
     * Translate a string or UserMessage to a string.
     *
     * @param string|UserMessage $message
     *
     * @return string
     */
    protected function translateExceptionPart(string|UserMessage $message): string
    {
        if ($message instanceof UserMessage) {
            return $this->translator->translate($message->message, $message->parameters);
        }

        return $this->translator->translate($message);
    }
}
