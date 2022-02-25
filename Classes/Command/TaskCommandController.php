<?php
declare(strict_types=1);

namespace Flowpack\Task\Command;

use Flowpack\Task\Domain\Model\TaskExecution;
use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Flowpack\Task\Domain\Runner\TaskRunner;
use Flowpack\Task\Domain\Scheduler\Scheduler;
use Flowpack\Task\Domain\Task\Task;
use Flowpack\Task\Domain\Task\TaskCollectionFactory;
use Flowpack\Task\Domain\Task\TaskExecutionHistory;
use Flowpack\Task\Domain\Task\TaskInterface;
use Flowpack\Task\Domain\Task\TaskStatus;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Neos\Flow\Cli\Exception\StopCommandException;

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

    /**
     * @Flow\Inject
     * @var TaskExecutionHistory
     */
    protected $taskExecutionHistory;

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
        $this->taskExecutionHistory->cleanup();
    }

    /**
     * Run a task directly
     *
     * @param string $taskIdentifier
     * @throws \Exception
     */
    public function runSingleCommand(string $taskIdentifier): void
    {
        $task = $this->getTaskByIdentifier($taskIdentifier);
        $this->scheduler->scheduleTask($task);
        $this->taskRunner->runTasks();
        $this->scheduler->scheduleTasks();
        $this->taskExecutionHistory->cleanup();
    }

    /**
     * Lists all defined tasks
     * @throws \Exception
     */
    public function listCommand(): void
    {
        $tasks = $this->taskCollectionFactory->buildTasksFromConfiguration()->toArray();
        if ($tasks === []) {
            $this->outputLine('<comment>No tasks configured yet</comment>');
            return;
        }
        $this->scheduler->scheduleTasks();

        $this->output->outputTable(array_map(function (TaskInterface $task) {
            /** @var TaskExecution $latestExecution */
            $latestExecution = $this->taskExecutionRepository->findLatestExecution($task, 1)->getFirst();
            return [
                $task->getIdentifier(),
                $task->getLabel(),
                $task->getCronExpression(),
                $task->getHandlerClass(),
                $latestExecution === null || $latestExecution->getEndTime() === null ? '-' : $latestExecution->getEndTime()->format('Y-m-d H:i:s') ?? $latestExecution->getStartTime()->format('Y-m-d H:i:s'),
                $latestExecution === null ? '-' : sprintf('<%s>%s</%s>', $this->lastExecutionStatusMapping[$latestExecution->getStatus()], $latestExecution->getStatus(), $this->lastExecutionStatusMapping[$latestExecution->getStatus()]),
                $latestExecution === null || $latestExecution->getDuration() === null ? '-' : number_format($latestExecution->getDuration(), 2) . ' s',
                $this->getNextExecutionInfo($task),
            ];
        }, $tasks),
            ['Identifier', 'Label', 'Cron Expression', 'Handler Class', 'Previous Run Date', 'Previous Run Status', 'Previous Run Duration', 'Next Run']
        );
    }

    /**
     * @param string $taskIdentifier
     * @throws \JsonException|StopCommandException
     */
    public function showCommand(string $taskIdentifier): void
    {
        $task = $this->getTaskByIdentifier($taskIdentifier);
        $this->outputLine(sprintf('<b>%s (%s)</b>', $task->getLabel(), $taskIdentifier));
        $this->outputLine(PHP_EOL . $task->getDescription() . PHP_EOL);

        $this->outputLine('<b>Task Info</b>');
        $this->output->outputTable(
            [
                ['Cron Expression', $task->getCronExpression()],
                ['First Execution', $task->getFirstExecution() === null ? '-' : $task->getFirstExecution()->format('Y-m-d H:i:s')],
                ['Last Execution', $task->getLastExecution() === null ? '-' : $task->getLastExecution()->format('Y-m-d H:i:s')],
                ['Handler Class', $task->getHandlerClass()],
                ['Workload', $task->getWorkload() !== null ? json_encode($task->getWorkload()->getData(), JSON_THROW_ON_ERROR + JSON_PRETTY_PRINT) : '-'],
                ['Next Run', $this->getNextExecutionInfo($task)],
            ]
        );

        $this->outputLine(PHP_EOL . '<b>Task Executions</b>');
        $taskExecutions = $this->taskExecutionRepository->findLatestExecution($task);

        if ($taskExecutions->count() === 0) {
            $this->outputLine('This task has not yet been executed.');
            return;
        }

        $this->output->outputTable(
            array_map(function (TaskExecution $execution) {
                return [
                    sprintf('<b>%s</b>', $execution->getScheduleTime()->format('Y-m-d H:i:s')),
                    number_format($execution->getDuration(), 2) . ' s',
                    sprintf('<%s>%s</%s> %s %s', $this->lastExecutionStatusMapping[$execution->getStatus()], $execution->getStatus(), $this->lastExecutionStatusMapping[$execution->getStatus()], $execution->getResult(), $execution->getException()),
                ];
            }, $taskExecutions->toArray()),
            ['Date','Run Duration', 'Status']
        );
    }

    /**
     * @param TaskInterface $task
     * @return string
     */
    private function getNextExecutionInfo(TaskInterface $task): string
    {
        $nextExecution = $this->taskExecutionRepository->findNextScheduled((new \DateTime())->add(new \DateInterval('P10Y')), [], $task);
        $nextExecutionInfo = 'Not Scheduled';
        if ($nextExecution instanceof TaskExecution) {
            $nextExecutionDate = $nextExecution->getScheduleTime()->format('Y-m-d H:i:s');
            $nextExecutionInfo = $nextExecution->getScheduleTime() < (new \DateTime()) ? sprintf('<error>%s (delayed)</error>', $nextExecutionDate) : $nextExecutionDate;
        }
        return $nextExecutionInfo;
    }

    /**
     * @param string $taskIdentifier
     * @return TaskInterface
     * @throws StopCommandException
     */
    private function getTaskByIdentifier(string $taskIdentifier): TaskInterface
    {
        try {
            return $this->taskCollectionFactory->buildTasksFromConfiguration()->getTask($taskIdentifier);
        } catch (\InvalidArgumentException $exception) {
            $this->outputLine('<error>No task with id "%s" is configured</error>', [$taskIdentifier]);
            $this->quit(1);
        }
    }
}
