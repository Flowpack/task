<?php
declare(strict_types=1);

namespace Flowpack\Task\TaskHandler;

use Flowpack\Task\Domain\Task\Workload;

interface TaskHandlerInterface
{

    /**
     * @return string Information about a successful task run
     */
    public function handle(Workload $workload): string;

}
