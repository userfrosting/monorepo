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
use UserFrosting\Config\Config;
use UserFrosting\Sprinkle\Core\Database\Models\Concerns\Prunable;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\PrunableModelInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Interfaces\SessionModelInterface;

/**
 * Session Class.
 *
 * Represents a session object as stored in the database.
 *
 * @mixin \Illuminate\Database\Query\Builder
 */
class Session extends Model implements PrunableModelInterface, SessionModelInterface
{
    use Prunable;

    /**
     * @var string The name of the table for the current model.
     */
    protected $table = 'sessions';

    /**
     * @var bool Session IDs are assigned by the session handler.
     */
    public $incrementing = false;

    /**
     * @var string The session identifier is a string.
     */
    protected $keyType = 'string';

    /**
     * @var bool Disable timestamps
     */
    public $timestamps = false;

    /**
     * {@inheritDoc}
     */
    public function prunable(): Builder
    {
        if (static::$ci === null) {
            throw new LogicException('The application container must be initialized before pruning sessions.');
        }

        /** @var Config */
        $config = static::$ci->get(Config::class);
        $minutes = $config->getInt('session.minutes') ?? 120;

        $query = $this->newQuery();
        $query->where('last_activity', '<=', Carbon::now()->subMinutes($minutes)->timestamp);

        return $query;
    }
}
