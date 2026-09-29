<?php

declare(strict_types=1);

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2024 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Session;

use Illuminate\Session\DatabaseSessionHandler as IlluminateDatabaseSessionHandler;
use SessionUpdateTimestampHandlerInterface;

class DatabaseSessionHandler extends IlluminateDatabaseSessionHandler implements SessionUpdateTimestampHandlerInterface
{
    public function validateId(string $sessionId): bool
    {
        return $this->getQuery()->where('id', $sessionId)->exists();
    }

    public function updateTimestamp(string $sessionId, string $data): bool
    {
        $updated = $this->getQuery()
            ->where('id', $sessionId)
            ->update(['last_activity' => $this->currentTime()]);

        if ($updated > 0 || $this->getQuery()->where('id', $sessionId)->exists()) {
            return true;
        }

        $this->setExists(false);

        return $this->write($sessionId, $data);
    }
}
