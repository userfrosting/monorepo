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

use Carbon\Carbon;
use Illuminate\Support\Collection;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\ThrottleModelInterface;

/**
 * Handles throttling (rate limiting) of specific types of requests.
 */
class Throttler
{
    /**
     * @var (ThrottleRule|null)[] An array mapping throttle names to throttle rules.
     */
    protected $throttleRules = [];

    /**
     * Create a new Throttler object.
     */
    public function __construct(
        protected ThrottleModelInterface $throttleModel
    ) {
    }

    /**
     * Add a throttling rule for a particular throttle event type.
     *
     * @param string            $type The type of throttle event to check against.
     * @param ThrottleRule|null $rule The rule to use when throttling this type of event.
     *
     * @return static
     */
    public function addThrottleRule(string $type, ?ThrottleRule $rule = null): static
    {
        $this->throttleRules[$type] = $rule;

        return $this;
    }

    /**
     * Check the current request against a specified throttle rule.
     *
     * @param string   $type        The type of throttle event to check against.
     * @param string[] $requestData Any additional request parameters to use in checking the throttle.
     *
     * @return int
     */
    public function getDelay(string $type, array $requestData = []): int
    {
        $throttleRule = $this->getRule($type);

        if (is_null($throttleRule)) {
            return 0;
        }

        // Get earliest time to start looking for throttleable events
        $startTime = Carbon::now()
            ->subSeconds($throttleRule->getInterval());

        // Fetch all throttle events of the specified type, that match the specified rule
        if ($throttleRule->getMethod() === 'ip') {
            /** @var Collection<int, ThrottleModelInterface> */
            $events = $this->throttleModel
                ->where('type', $type)
                ->where('created_at', '>', $startTime)
                ->where('ip', $_SERVER['REMOTE_ADDR'])
                ->get();
        } else {
            /** @var Collection<int, ThrottleModelInterface> */
            $events = $this->throttleModel
                ->where('type', $type)
                ->where('created_at', '>', $startTime)
                ->get();

            // Filter out only events that match the required JSON data
            $events = $events->filter(function (ThrottleModelInterface $item, int $key) use ($requestData) {
                /** @var \stdClass */
                $data = json_decode($item->request_data);

                // If a field is not specified in the logged data, or it doesn't match the value we're searching for,
                // then filter out this event from the collection.
                foreach ($requestData as $name => $value) {
                    if (!isset($data->$name) || ($data->$name !== $value)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Check the collection of events against the specified throttle rule.
        return $this->computeDelay($events, $throttleRule);
    }

    /**
     * Get a registered rule of a particular type.
     *
     * @param string $type
     *
     * @throws ThrottlerException
     *
     * @return ThrottleRule|null
     */
    public function getRule(string $type): ?ThrottleRule
    {
        if (!array_key_exists($type, $this->throttleRules)) {
            throw new ThrottlerException("The throttling rule for '$type' could not be found.");
        }

        return $this->throttleRules[$type];
    }

    /**
     * Get the current throttling rules.
     *
     * @return (ThrottleRule|null)[]
     */
    public function getThrottleRules(): array
    {
        return $this->throttleRules;
    }

    /**
     * Log a throttleable event to the database.
     *
     * @param string   $type        the type of event
     * @param string[] $requestData an array of field names => values that are relevant to throttling for this event (e.g. username, email, etc).
     *
     * @return static
     */
    public function logEvent($type, $requestData = []): static
    {
        // Just a check to make sure the rule exists
        $throttleRule = $this->getRule($type);

        if (is_null($throttleRule)) {
            return $this;
        }

        $event = new $this->throttleModel([
            'type'         => $type,
            'ip'           => $_SERVER['REMOTE_ADDR'],
            'request_data' => json_encode($requestData),
        ]);

        $event->save();

        return $this;
    }

    /**
     * Returns the current delay for a specified throttle rule.
     *
     * @param Collection<int, ThrottleModelInterface> $events       a Collection of throttle events.
     * @param ThrottleRule                            $throttleRule a rule representing the strategy to use for throttling a particular type of event.
     *
     * @return int seconds remaining until a particular event is permitted to be attempted again.
     */
    protected function computeDelay(Collection $events, ThrottleRule $throttleRule): int
    {
        // If no matching events found, then there is no delay
        if ($events->count() === 0) {
            return 0;
        }

        // Great, now we compare our delay against the most recent attempt
        /** @var ThrottleModelInterface */
        $lastEvent = $events->last();

        return $throttleRule->getDelay($lastEvent->created_at, $events->count());
    }
}
