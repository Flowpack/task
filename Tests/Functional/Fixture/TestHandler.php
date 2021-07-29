<?php
declare(strict_types=1);

namespace Flowpack\Task\Tests\Functional\Fixture;

use Flowpack\Task\Domain\Task\WorkloadInterface;
use Flowpack\Task\TaskHandler\TaskHandlerInterface;

class TestHandler implements TaskHandlerInterface
{

    public function handle(WorkloadInterface $workload): string
    {
        return 'Successfull! ' . $workload->jsonSerialize();
    }
}
