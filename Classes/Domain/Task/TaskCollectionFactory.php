<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

use Cron\CronExpression;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Psr\Log\LoggerInterface;

/**
 * @Flow\Scope("singleton")
 */
class TaskCollectionFactory
{
    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $systemLogger;

    /**
     * @Flow\InjectConfiguration(package="Flowpack.Task", path="tasks")
     */
    protected array $taskConfigurations = [];

    protected ?TaskCollection $taskCollection = null;

    public function buildTasksFromConfiguration(): TaskCollection
    {
        if ($this->taskCollection instanceof TaskCollection) {
            return $this->taskCollection;
        }

        $this->taskCollection = new TaskCollection();

        foreach ($this->taskConfigurations as $taskIdentifier => $taskConfiguration) {

            $cronExpressionPattern = $taskConfiguration['cronExpression'] ?? '';
            $cronExpression = $cronExpressionPattern !== '' ? new CronExpression($cronExpressionPattern) : null;

            if (!class_exists($taskConfiguration['handlerClass'])) {
                $this->systemLogger->info(sprintf('Taskhandler class not found - Task "%s" is ignored', $taskConfiguration['handlerClass']), LogEnvironment::fromMethodName(__METHOD__));
                continue;
            }

            $this->taskCollection->set($taskIdentifier, new Task(
                $taskIdentifier,
                $cronExpression,
                $taskConfiguration['handlerClass'],
                $taskConfiguration['label'] ?? $taskIdentifier,
                $taskConfiguration['description'] ?? '',
                new Workload($taskConfiguration['workload'] ?? []),
                new \DateTime($taskConfiguration['firstExecution'] ?? 'now'),
                ($taskConfiguration['lastExecution'] ?? null) === null ? null : new \DateTime($taskConfiguration['lastExecution'])
            ));
        }

        return $this->taskCollection;
    }
}
