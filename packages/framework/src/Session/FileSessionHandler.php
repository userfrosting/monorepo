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

use Illuminate\Session\FileSessionHandler as IlluminateFileSessionHandler;
use SessionUpdateTimestampHandlerInterface;

class FileSessionHandler extends IlluminateFileSessionHandler implements SessionUpdateTimestampHandlerInterface
{
    public function validateId(string $sessionId): bool
    {
        return $this->files->isFile($this->path . '/' . $sessionId);
    }

    public function updateTimestamp(string $sessionId, string $data): bool
    {
        $path = $this->path . '/' . $sessionId;

        if (!$this->files->isFile($path)) {
            return $this->write($sessionId, $data);
        }

        $updated = touch($path);
        clearstatcache(true, $path);

        return $updated;
    }
}
