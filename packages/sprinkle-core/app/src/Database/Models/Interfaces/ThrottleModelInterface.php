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
 * Throttle Class.
 *
 * Represents a throttleable request from a user agent.
 *
 * @mixin \Illuminate\Database\Eloquent\Builder
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property int         $id
 * @property string      $type
 * @property string|null $ip
 * @property string      $request_data
 * @property Carbon      $created_at
 * @property Carbon      $updated_at
 */
interface ThrottleModelInterface extends MorphableModelInterface
{
}
