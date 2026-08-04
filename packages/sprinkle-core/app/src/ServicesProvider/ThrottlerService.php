<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use InvalidArgumentException;
use UserFrosting\Config\Config;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\ThrottleModelInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Throttle;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottleRule;

/**
 * Request throttler.
 *
 * Throttles (rate-limits) requests of a predefined type, with rules defined in site config.
 */
class ThrottlerService implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            Throttler::class => function (ThrottleModelInterface $model, Config $config) {
                $throttler = new Throttler($model);

                // Skip if no throttles are defined in the config
                if (!$config->has('throttles')) {
                    return $throttler;
                }

                // Get throttles configuration from config
                $throttleConfig = $config->get('throttles');
                if (!is_array($throttleConfig)) {
                    throw new InvalidArgumentException('The throttles configuration must be an array.');
                }

                // Validate and add each throttle rule to the throttler
                foreach ($throttleConfig as $type => $rule) {
                    if (!is_string($type)) {
                        throw new InvalidArgumentException('Throttle rule names must be strings.');
                    }

                    // If the rule type is present, but the rule is null, we
                    // still want to add it to the throttler with a null rule.
                    // Allows config override to disable throttling for a specific type.
                    if ($rule === null) {
                        $throttler->addThrottleRule($type, null);

                        continue;
                    }

                    // Validate the rule structure
                    if (
                        !is_array($rule)
                        || !array_key_exists('method', $rule)
                        || !array_key_exists('interval', $rule)
                        || !array_key_exists('delays', $rule)
                    ) {
                        throw new InvalidArgumentException("Invalid throttle rule '$type'.");
                    }

                    $method = $rule['method'];
                    $interval = $rule['interval'];
                    $rawDelays = $rule['delays'];

                    // Validate the types of the rule fields
                    if (!is_string($method) || !is_int($interval) || !is_array($rawDelays)) {
                        throw new InvalidArgumentException("Invalid throttle rule '$type'.");
                    }

                    $delays = [];
                    foreach ($rawDelays as $attempts => $delay) {
                        if (!is_int($attempts) || !is_int($delay)) {
                            throw new InvalidArgumentException("Invalid throttle rule '$type'.");
                        }

                        $delays[$attempts] = $delay;
                    }

                    $throttler->addThrottleRule($type, new ThrottleRule($method, $interval, $delays));
                }

                return $throttler;
            },

            // Map Throttle Model
            ThrottleModelInterface::class  => \DI\autowire(Throttle::class),
        ];
    }
}
