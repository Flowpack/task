<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

/*
 * This file is part of the Flowpack.Task package.
 *
 * (c) Contributors of the Flowpack Team - flowpack.org
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Flowpack\Task\Domain\Model\TaskExecution;
use Neos\Flow\Annotations as Flow;
use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Neos\Flow\Log\Utility\LogEnvironment;
use Psr\Log\LoggerInterface;

class TaskExecutionHistory
{

    /**
     * @Flow\Inject
     * @var TaskExecutionRepository
     */
    protected $taskExecutionRepository;

    /**
     * @Flow\InjectConfiguration(package="Flowpack.Task", path="keepTaskExecutionHistory")
     * @var int
     */
    protected int $keepTaskExecutionHistory = 3;

    /**
     * @Flow\Inject
     * @var TaskCollectionFactory
     */
    protected $taskCollectionFactory;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function cleanup(): void
    {
        $removedTaskExecutions = 0;

        /** @var Task $task */
        foreach ($this->taskCollectionFactory->buildTasksFromConfiguration() as $task) {
            /** @var TaskExecution $taskExecution */
            foreach ($this->taskExecutionRepository->findLatestExecution($task, 0, $this->keepTaskExecutionHistory) as $taskExecution) {
                $this->taskExecutionRepository->remove($taskExecution);
                $removedTaskExecutions++;
            }
        }

        if ($removedTaskExecutions > 0) {
            $this->logger->info(sprintf('Removed %s completed task executions', $removedTaskExecutions), LogEnvironment::fromMethodName(__METHOD__));
        }
    }
}
