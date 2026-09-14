<?php

declare(strict_types=1);

/*
 * UserFrosting Account Sprinkle (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/sprinkle-account
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/sprinkle-account/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Log;

use LogicException;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;
use Psr\Log\LogLevel;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\ActivityInterface;

/**
 * Monolog handler for storing user activities to the database.
 *
 * @deprecated 6.1 Use ActivityRecorder instead
 */
class UserActivityDatabaseHandler extends AbstractProcessingHandler
{
    /**
     * @var ActivityInterface
     */
    protected ActivityInterface $model;

    /**
     * Create a new DatabaseHandler object.
     *
     * @param ActivityInterface            $model
     * @param int|string|Level|LogLevel::* $level  The minimum logging level at which this handler will be triggered
     * @param bool                         $bubble Whether the messages that are handled can bubble up the stack or not
     *
     * @phpstan-param value-of<Level::VALUES>|value-of<Level::NAMES>|Level|LogLevel::* $level
     */
    public function __construct(
        ActivityInterface $model,
        int|string|Level $level = Level::Debug,
        bool $bubble = true
    ) {
        $this->model = $model;
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(LogRecord $record): void
    {
        // Trigger deprecation warning
        trigger_error('UserActivityLogger/UserActivityLoggerInterface is deprecated since UserFrosting 6.1 and will be removed in 6.2. Use ActivityRecorder instead.', E_USER_DEPRECATED);

        if (!is_array($record['context']) || !isset($record['context']['user_id'])) {
            throw new LogicException('UserActivityLogger requires a `user_id` to be set in the context.');
        }

        $log = new $this->model([
            'ip_address'  => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
            'user_id'     => $record['context']['user_id'],
            'type'        => $record['context']['type'] ?? 'undefined',
            'occurred_at' => $record['datetime'],
            'description' => $record['message'],
        ]);
        $log->save();
    }
}
