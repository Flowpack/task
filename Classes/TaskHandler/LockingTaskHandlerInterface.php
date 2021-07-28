<?php
declare(strict_types=1);

namespace Flowpack\Task\TaskHandler;

use Flowpack\Task\Domain\Task\Workload;
use Task\Handler\TaskHandlerInterface;

/**
 * Handler which implements this interface locks other executions during run.
 */
interface LockingTaskHandlerInterface extends TaskHandlerInterface
{
    /**
     * Returns lock-key which defines the locked resources.
     *
     * @param Workload $workload
     *
     * @return string
     */
    public function getLockIdentifier(Workload $workload): string;
}
