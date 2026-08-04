<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error\Renderer;

use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use UserFrosting\Sprinkle\Core\Util\Message\Message;

/**
 * Plain Text Error Renderer.
 */
final class PlainTextRenderer implements ErrorRendererInterface
{
    /**
     * {@inheritDoc}
     */
    public function render(
        ServerRequestInterface $request,
        Throwable $exception,
        Message $userMessage,
        int $statusCode,
        bool $displayErrorDetails = false
    ): string {
        if ($displayErrorDetails) {
            return $this->formatExceptionBody($exception);
        }

        return $exception->getMessage();
    }

    /**
     * Format Exception Body.
     *
     * @param Throwable $e
     *
     * @return string
     */
    public function formatExceptionBody(Throwable $e): string
    {
        $text = 'UserFrosting Application Error:' . PHP_EOL;
        $text .= $this->formatExceptionFragment($e);

        while ($e = $e->getPrevious()) {
            $text .= PHP_EOL . 'Previous Error:' . PHP_EOL;
            $text .= $this->formatExceptionFragment($e);
        }

        return $text;
    }

    /**
     * @param Throwable $e
     *
     * @return string
     */
    public function formatExceptionFragment(Throwable $e): string
    {
        $text = sprintf('Type: %s' . PHP_EOL, get_class($e));

        if (($code = $e->getCode()) !== 0) {
            $text .= sprintf('Code: %s' . PHP_EOL, $code);
        }
        if (($message = $e->getMessage()) !== '') {
            $text .= sprintf('Message: %s' . PHP_EOL, htmlentities($message));
        }
        if (($file = $e->getFile()) !== '') {
            $text .= sprintf('File: %s' . PHP_EOL, $file);
        }
        if (($line = $e->getLine()) !== 0) {
            $text .= sprintf('Line: %s' . PHP_EOL, $line);
        }
        if (($trace = $e->getTraceAsString()) !== '') {
            $text .= sprintf('Trace: %s', $trace);
        }

        return $text;
    }
}
