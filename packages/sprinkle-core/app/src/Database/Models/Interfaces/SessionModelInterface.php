<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Models\Interfaces;

use Carbon\Carbon;

/**
 * Session Class.
 *
 * Represents a session object as stored in the database.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property int         $id
 * @property int|null    $user_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string      $payload
 * @property Carbon      $last_activity
 */
interface SessionModelInterface extends MorphableModelInterface
{
}
