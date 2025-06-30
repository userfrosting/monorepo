<?php

declare(strict_types=1);

/*
 * UserFrosting Core Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-core
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-core/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use Stringable;
use UserFrosting\Sprinkle\Core\Util\Message\Message;

/**
 * Api response class.
 *
 * This class is used to create a standardized response for API endpoints.
 * Can be used to return a JSON response with a title and description.
 * Implements Stringable to allow easy conversion to JSON string.
 */
class ApiResponse extends Message implements Stringable
{
    public function __toString(): string
    {
        $data = [
            'title'       => $this->title,
            'description' => $this->description,
        ];

        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
