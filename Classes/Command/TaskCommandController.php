<?php
declare(strict_types=1);

namespace Flowpack\Task\Command;

use Flowpack\Task\Domain\Model\TaskExecution;
use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Flowpack\Task\Domain\Runner\TaskRunner;
use Flowpack\Task\Domain\Scheduler\Scheduler;
use Flowpack\Task\Domain\Task\TaskCollectionFactory;
use Flowpack\Task\Domain\Task\TaskInterface;
use Flowpack\Task\Domain\Task\TaskStatus;
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
     * @Flow\Inject
     * @var TaskExecutionRepository
     */
    protected $taskExecutionRepository;

    /**
     * @Flow\Inject
     * @var TaskRunner
     */
    protected $taskRunner;

    protected array $lastExecutionStatusMapping = [
        TaskStatus::FAILED => 'error',
        TaskStatus::COMPLETED => 'success',
        TaskStatus::RUNNING => 'em',
        TaskStatus::ABORTED => 'strike'
    ];

    /**
     * @throws \Exception
     */
    public function runCommand(): void
    {
        $this->scheduler->scheduleTasks();
        $this->taskRunner->runTasks();
    }

    /**
     * Lists all defined tasks
     */
    public function listCommand(): void
    {
        $this->scheduler->scheduleTasks();

        $this->output->outputTable(array_map(function (TaskInterface $task) {
            /** @var TaskExecution $latestExecution */
            $latestExecution = $this->taskExecutionRepository->findLatest($task)->getFirst();
            $nextExecution = $this->taskExecutionRepository->findNextScheduled((new \DateTime())->add(new \DateInterval('P10Y')), [], $task);
            $nextExecutionInfo = 'Not Scheduled';
            if ($nextExecution instanceof TaskExecution) {
                $nextExecutionDate = $nextExecution->getScheduleTime()->format('Y-m-d H:i:s');
                $nextExecutionInfo = $nextExecution->getScheduleTime() < (new \DateTime()) ? sprintf('<error>%s (delayed)</error>', $nextExecutionDate) : $nextExecutionDate;
            }
            return [
                $task->getIdentifier(),
                $task->getLabel(),
                $task->getCronExpression()->getExpression(),
                $task->getHandlerClass(),
                $latestExecution === null ? '-' : $latestExecution->getEndTime()->format('Y-m-d H:i:s') ?? $latestExecution->getStartTime()->format('Y-m-d H:i:s'),
                $latestExecution === null ? '-' : sprintf('<%s>%s</%s>', $this->lastExecutionStatusMapping[$latestExecution->getStatus()], $latestExecution->getStatus(), $this->lastExecutionStatusMapping[$latestExecution->getStatus()]),
                $latestExecution === null || $latestExecution->getDuration() === null ? '-' : number_format($latestExecution->getDuration(), 2) . ' s',
                $nextExecutionInfo,
            ];
        }, $this->taskCollectionFactory->buildTasksFromConfiguration()->toArray()),
            ['Identifier', 'Label', 'Cron Expression', 'Handler Class', 'Previous Run Date', 'Previous Run Status', 'Previous Run Duration', 'Next Run']
        );
    }
}
