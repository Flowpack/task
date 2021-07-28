<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

final class TaskStatus
{
    public const PLANNED = 'planned';

    public const RUNNING = 'running';

    public const COMPLETED = 'completed';

    public const ABORTED = 'aborted';

    public const FAILED = 'failed';

    /**
     * Private constructor.
     */
    private function __construct()
    {
    }
}
