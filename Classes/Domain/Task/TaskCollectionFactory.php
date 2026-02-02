<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

use Cron\CronExpression;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\ObjectManagerInterface;

/**
 * @Flow\Scope("singleton")
 */
class TaskCollectionFactory
{

    /**
     * @Flow\InjectConfiguration(package="Flowpack.Task", path="tasks")
     */
    protected array $taskConfigurations = [];

    protected ?TaskCollection $taskCollection = null;

    private ObjectManagerInterface $objectManager;

    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    public function buildTasksFromConfiguration(): TaskCollection
    {
        if ($this->taskCollection instanceof TaskCollection) {
            return $this->taskCollection;
        }

        $this->taskCollection = new TaskCollection();

        foreach ($this->taskConfigurations as $taskIdentifier => $taskConfiguration) {

            $cronExpressionPattern = $taskConfiguration['cronExpression'] ?? '';
            $cronExpression = $cronExpressionPattern !== '' ? new CronExpression($cronExpressionPattern) : null;

            $this->taskCollection->set($taskIdentifier, new Task(
                $taskIdentifier,
                $cronExpression,
                $taskConfiguration['handlerClass'],
                $taskConfiguration['label'] ?? $taskIdentifier,
                $taskConfiguration['description'] ?? '',
                $this->objectManager->get(WorkloadInterface::class, $taskConfiguration['workload'] ?? []),
                new \DateTime($taskConfiguration['firstExecution'] ?? 'now'),
                ($taskConfiguration['lastExecution'] ?? null) === null ? null : new \DateTime($taskConfiguration['lastExecution'])
            ));
        }

        return $this->taskCollection;
    }
}
