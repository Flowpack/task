<?php
declare(strict_types=1);

namespace Flowpack\Task\TaskHandler;

/**
 * Indicates that this handler can be retried.
 */
interface RetryTaskHandlerInterface
{
    /**
     * Returns maximum attempts to pass tasks with this handler.
     */
    public function getMaximumAttempts(): int;
}
