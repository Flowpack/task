<?php
declare(strict_types=1);

namespace Flowpack\Task\TaskHandler;

use Flowpack\Task\Domain\Task\WorkloadInterface;

/**
 * Handler which implements this interface locks other executions during run.
 */
interface LockingTaskHandlerInterface extends TaskHandlerInterface
{
    /**
     * Returns lock-key which defines the locked resources.
     *
     * @param WorkloadInterface $workload
     *
     * @return string
     */
    public function getLockIdentifier(WorkloadInterface $workload): string;
}
