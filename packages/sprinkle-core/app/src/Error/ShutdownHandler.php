<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Error;

use Psr\Http\Message\ServerRequestInterface;
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Util\DeterminesContentTypeTrait;

/**
 * Registers a handler to be invoked whenever the application shuts down.
 * If it shut down due to a fatal error, will generate a clean error message.
 *
 * @see https://www.slimframework.com/docs/v4/objects/application.html#advanced-notices-and-warnings-handling
 */
class ShutdownHandler
{
    use DeterminesContentTypeTrait;

    /**
     * @var string[] Error types this handler handle.
     */
    protected array $errorTypes = [
        E_ERROR             => 'Fatal error',
        E_PARSE             => 'Parse error',
        E_CORE_ERROR        => 'PHP core error',
        E_COMPILE_ERROR     => 'Zend compile error',
        E_RECOVERABLE_ERROR => 'Catchable fatal error',
    ];

    /**
     * Known handled content types.
     *
     * @var string[]
     */
    protected array $knownContentTypes = [
        'application/json',
        'text/html',
        'text/plain',
    ];

    public function __construct(
        protected Config $config,
        protected ServerRequestInterface $request,
    ) {
    }

    /**
     * Register this class with the shutdown handler.
     */
    public function register(): void
    {
        register_shutdown_function([$this, 'handle']);
    }

    /**
     * Set up the fatal error handler, so that we get a clean error message and alert instead of a WSOD.
     */
    public function handle(): void
    {
        $error = error_get_last();

        // Handle fatal errors and parse errors
        if ($error !== null && in_array($error['type'], array_keys($this->errorTypes), true)) {
            // Default to 'text/plain' if in CLI
            if (php_sapi_name() === 'cli') {
                $contentType = 'text/plain';
            } else {
                $contentType = $this->determineContentType($this->request, $this->knownContentTypes, 'text/plain');
            }

            // Get error message based on
            $errorMessage = match ($contentType) {
                'application/json' => $this->buildJsonError($error),
                'text/html'        => $this->buildHtmlError($error),
                default            => $this->buildTxtError($error),
            };

            // For CLI, just print the message and exit.
            if (php_sapi_name() === 'cli') {
                $errorMessage .= PHP_EOL;
            }

            // For all other environments, print a debug response for the requested data type
            $this->terminate($errorMessage);
        }
    }

    /**
     * Display the error message and terminate the process.
     *
     * The exist is separated in it's own class to allows testing.
     *
     * @see https://stackoverflow.com/a/21578225/445757
     *
     * @codeCoverageIgnore
     *
     * @param string $errorMessage
     *
     * @return never
     */
    protected function terminate(string $errorMessage): void
    {
        echo $errorMessage;

        header('HTTP/1.1 500 Internal Server Error');
        exit;
    }

    /**
     * Build an error response of the appropriate type as determined by the request's Accept header.
     *
     * @param array{type: int, message: string, file: string, line: int} $error
     *
     * @return string
     */
    public function buildJsonError(array $error): string
    {
        if ($this->shouldDisplayError()) {
            // Translate type int to string
            $error['type'] = $this->errorTypes[$error['type']];

            return json_encode($error, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        } else {
            return json_encode([
                'message' => "Oops, looks like our server might have goofed. If you're an admin, please ensure that `php.log_errors` is enabled, and then check the PHP error log.",
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        }
    }

    /**
     * Build an error response of the appropriate type as determined by the request's Accept header.
     *
     * @param array{type: int, message: string, file: string, line: int} $error
     *
     * @return string
     */
    public function buildTxtError(array $error): string
    {
        if ($this->shouldDisplayError()) {
            $file = $error['file'];
            $line = (string) $error['line'];
            $message = $error['message'];
            $type = $this->errorTypes[$error['type']];

            return "$type: $message in $file on line $line";
        } else {
            return "Oops, looks like our server might have goofed. If you're an admin, please ensure that `php.log_errors` is enabled, and then check the PHP error log.";
        }
    }

    /**
     * Build an HTML error page from an error string.
     *
     * @param array{type: int, message: string, file: string, line: int} $error
     *
     * @return string
     */
    public function buildHtmlError(array $error): string
    {
        // Build the appropriate error message (debug or client)
        if ($this->shouldDisplayError()) {
            $message = $this->buildHttpErrorInfoMessage($error);
        } else {
            $message = "Oops, looks like our server might have goofed. If you're an admin, please ensure that <code>php.log_errors</code> is enabled, and then check the <strong>PHP</strong> error log.";
        }

        $title = 'UserFrosting Application Error';
        $html = "<p>$message</p>";

        return sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            '<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,' .
            'sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}' .
            '</style></head><body><h1>%s</h1>%s</body></html>',
            $title,
            $title,
            $html
        );
    }

    /**
     * Build the error message string.
     *
     * @param array{type: int, message: string, file: string, line: int} $error
     *
     * @return string
     */
    protected function buildHttpErrorInfoMessage(array $error): string
    {
        $file = $error['file'];
        $line = (string) $error['line'];
        $message = $error['message'];
        $type = $this->errorTypes[$error['type']];

        return "<strong>$type</strong>: $message in <strong>$file</strong> on line <strong>$line</strong>";
    }

    /**
     * Should display full error (true) or not (false).
     *
     * @return bool
     */
    protected function shouldDisplayError(): bool
    {
        return (bool) $this->config->get('debug.exception');
    }
}
