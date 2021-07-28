<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Runner;

use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Flowpack\Task\TaskHandler\LockingTaskHandlerInterface;
use Flowpack\Task\TaskHandler\TaskHandlerFactory;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Psr\Log\LoggerInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;

class PendingExecutionFinder
{
    /**
     * @Flow\Inject
     * @var TaskExecutionRepository
     */
    protected $taskExecutionRepository;

    /**
     * @Flow\Inject
     * @var TaskHandlerFactory
     */
    protected $taskHandlerFactory;

    /**
     * @Flow\Inject
     * @var SemaphoreStore
     */
    protected $semaphoreStore;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function findNext(): \Generator
    {
        $runTime = new \DateTime();

        $logFactory = new LockFactory($this->semaphoreStore);

        $skippedExecutions = [];
        while ($execution = $this->taskExecutionRepository->findNextScheduled($runTime, $skippedExecutions)) {
            $handler = $this->taskHandlerFactory->get($execution->getHandlerClass());

            if (!$handler instanceof LockingTaskHandlerInterface) {
                yield $execution;
                continue;
            }

            $lock = $logFactory->createLock($handler->getLockIdentifier($execution->getWorkload()));

            if ($lock->acquire()) {
                $skippedExecutions[] = $execution;
                $this->logger->warning(sprintf('Execution "%s" is locked and skipped.', $execution->getTaskIdentifier()), LogEnvironment::fromMethodName(__METHOD__));
                continue;
            }

            yield $execution;

            $lock->release();
        }
    }
}
