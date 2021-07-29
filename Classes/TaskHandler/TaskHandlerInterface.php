<?php
declare(strict_types=1);

namespace Flowpack\Task\TaskHandler;

use Flowpack\Task\Domain\Task\WorkloadInterface;

interface TaskHandlerInterface
{

    /**
     * @return string Information about a successful task run
     */
    public function handle(WorkloadInterface $workload): string;

}
