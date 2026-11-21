<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use LogicException;
use UserFrosting\Sprinkle\Core\Database\Models\Concerns\Prunable;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\PrunableModelInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\ThrottleModelInterface;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;

/**
 * Throttle Class.
 *
 * Represents a throttleable request from a user agent.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 */
class Throttle extends Model implements PrunableModelInterface, ThrottleModelInterface
{
    use Prunable;

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'throttles';

    /**
     * @var array<int, string> The attributes that are mass assignable.
     */
    protected $fillable = [
        'type',
        'ip',
        'request_data',
    ];

    /**
     * {@inheritDoc}
     */
    public function prunable(): Builder
    {
        if (static::$ci === null) {
            throw new LogicException('The application container must be initialized before pruning throttles.');
        }

        /** @var Throttler */
        $throttler = static::$ci->get(Throttler::class);
        $retentionSeconds = 0;

        foreach ($throttler->getThrottleRules() as $rule) {
            if ($rule !== null) {
                $maxDelay = max(array_merge([0], $rule->getDelays()));
                $retentionSeconds = max($retentionSeconds, $rule->getInterval() + $maxDelay);
            }
        }

        $query = $this->newQuery();
        $query->where('created_at', '<', Carbon::now()->subSeconds($retentionSeconds));

        return $query;
    }
}
