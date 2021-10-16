<?php

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2021 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Log\DebugLogger;
use UserFrosting\Sprinkle\Core\Log\ErrorLogger;
use UserFrosting\Sprinkle\Core\Log\MailLogger;
use UserFrosting\Sprinkle\Core\Log\MixedFormatter;
use UserFrosting\Sprinkle\Core\Log\QueryLogger;
use UserFrosting\Support\Repository\Repository as Config;

class LoggersService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            /*
            * Debug logging with Monolog.
            *
            * Extend this service to push additional handlers onto the 'debug' log stack.
            *
            * @return \Monolog\Logger
            */
            DebugLogger::class => function (StreamHandler $handler, MixedFormatter $formatter) {
                $handler->setFormatter($formatter);

                $logger = new DebugLogger('debug');
                $logger->pushHandler($handler);

                return $logger;
            },

            /*
            * Error logging with Monolog.
            *
            * Extend this service to push additional handlers onto the 'error' log stack.
            *
            * @return \Monolog\Logger
            */
            ErrorLogger::class => function (StreamHandler $handler, LineFormatter $formatter) {
                $handler->setFormatter($formatter);
                $handler->setLevel(Logger::WARNING);

                $logger = new ErrorLogger('errors');
                $logger->pushHandler($handler);

                return $logger;
            },

            /*
             * Mail logging service.
             *
             * PHPMailer will use this to log SMTP activity.
             * Extend this service to push additional handlers onto the 'mail' log stack.
             *
             * @return \Monolog\Logger
             */
            MailLogger::class => function (StreamHandler $handler, LineFormatter $formatter) {
                $handler->setFormatter($formatter);

                $logger = new MailLogger('mail');
                $logger->pushHandler($handler);

                return $logger;
            },

            /*
             * Laravel query logging with Monolog.
             *
             * Extend this service to push additional handlers onto the 'query' log stack.
             *
             * @return \Monolog\Logger
             */
            QueryLogger::class => function (StreamHandler $handler, MixedFormatter $formatter) {
                $handler->setFormatter($formatter);

                $logger = new QueryLogger('query');
                $logger->pushHandler($handler);

                return $logger;
            },

            // Define formatter with `allowInlineLineBreaks` by default
            LineFormatter::class  => \DI\create()->constructor(null, null, true),
            MixedFormatter::class => \DI\create()->constructor(null, null, true),

            // Define common StreamHandler with .
            StreamHandler::class => function (Config $config) {
                return new StreamHandler($config->get('logger.streamHandler.stream'));
            }
        ];
    }
}
