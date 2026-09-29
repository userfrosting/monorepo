<?php

declare(strict_types=1);

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
