<?php
declare(strict_types=1);

namespace Flowpack\Task\Command;

use Flowpack\Task\Domain\Scheduler\Scheduler;
use Flowpack\Task\Domain\Task\TaskCollectionFactory;
use Flowpack\Task\Domain\Task\TaskInterface;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

class TaskCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var TaskCollectionFactory
     */
    protected $taskCollectionFactory;

    /**
     * @Flow\Inject
     * @var Scheduler
     */
    protected $scheduler;


    /**
     * @throws \Exception
     */
    public function runCommand(): void
    {
        $this->scheduler->scheduleTasks();
    }

    /**
     * Lists all defined tasks
     */
    public function listCommand(): void
    {
        $this->output->outputTable(array_map(static function (TaskInterface $task) {
            return [
                $task->getLabel(),
                $task->getHandlerClass(),
                $task->getFirstExecution()->format('Y-m-d H:i:s'),
                $task->getLastExecution() instanceof \DateTime ? $task->getLastExecution()->format('Y-m-d H:i:s') : '-',
            ];
            }, $this->taskCollectionFactory->buildTasksFromConfiguration()->toArray()),
            ['Label', 'Handler Class', 'First Execution', 'Last Execution']
        );
    }
}
