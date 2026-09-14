<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\ServicesProvider;

use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorder;
use UserFrosting\Sprinkle\Account\Log\ActivityRecorderInterface;
use UserFrosting\Sprinkle\Account\Log\AuthLogger;
use UserFrosting\Sprinkle\Account\Log\AuthLoggerInterface;
use UserFrosting\Sprinkle\Account\Log\UserActivityLogger;
use UserFrosting\Sprinkle\Account\Log\UserActivityLoggerInterface;

final class LoggersService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            AuthLoggerInterface::class         => \DI\autowire(AuthLogger::class),
            UserActivityLoggerInterface::class => \DI\autowire(UserActivityLogger::class), // @phpstan-ignore-line classConstant.deprecatedInterface classConstant.deprecatedClass
            ActivityRecorderInterface::class   => \DI\autowire(ActivityRecorder::class),
        ];
    }
}
