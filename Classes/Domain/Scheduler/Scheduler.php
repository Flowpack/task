<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Scheduler;

use Flowpack\Task\Domain\Model\TaskExecution;
use Flowpack\Task\Domain\Task\Task;
use Flowpack\Task\Domain\Task\TaskCollectionFactory;
use Neos\Flow\Annotations as Flow;
use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;

class Scheduler
{
    /**
     * @Flow\Inject
     * @var TaskCollectionFactory
     */
    protected $taskCollectionFactory;

    /**
     * @Flow\Inject
     * @var TaskExecutionRepository
     */
    protected $taskExecutionRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @throws \Exception
     */
    public function scheduleTasks(): void
    {
        foreach ($this->taskCollectionFactory->buildTasksFromConfiguration()->filterEndBeforeNow() as $task) {
            $this->scheduleTask($task);
        }

        $this->persistenceManager->persistAll();
    }

    public function scheduleTaskForDate(string $taskIdentifier, \DateTime $runDate): void
    {
        $task = $this->taskCollectionFactory->buildTasksFromConfiguration()->getTask($taskIdentifier);
        $task->setCronExpression(null);
        $task->setFirstExecution($runDate);
        $this->taskExecutionRepository->removePlannedTask($task);
        $this->persistenceManager->persistAll();
        $this->scheduleTask($task);
    }

    /**
     * Schedule execution for given task.
     *
     * @param Task $task
     * @throws \Exception
     */
    public function scheduleTask(Task $task): void
    {
        $scheduledTasks = $this->taskExecutionRepository->findPending($task);

        if ($scheduledTasks->count() > 0) {
            return;
        }

        if ($task->getCronExpression() === null && count($this->taskExecutionRepository->findByTask($task)) > 0) {
            return;
        }

        $nextCronRunDate = $task->getCronExpression() ? $task->getCronExpression()->getNextRunDate() : null;

        if ($nextCronRunDate !== null && $nextCronRunDate > $task->getFirstExecution()) {
            $scheduleTime = $task->getCronExpression()->getNextRunDate();
        } else {
            $scheduleTime = $task->getFirstExecution();
        }

        $nextExecution = new TaskExecution($task, $scheduleTime);
        $this->taskExecutionRepository->add($nextExecution);
    }

}
