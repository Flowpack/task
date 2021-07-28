<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Runner;

use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\TransactionRequiredException;
use Exception;
use Flowpack\Task\Domain\Model\TaskExecution;
use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Flowpack\Task\Domain\Task\TaskStatus;
use Flowpack\Task\Exceptions\TaskExitException;
use Flowpack\Task\Exceptions\TaskFailedException;
use Flowpack\Task\Exceptions\TaskRetryException;
use Flowpack\Task\TaskHandler\TaskHandlerFactory;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Log\Utility\LogEnvironment;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Persistence\Exception\IllegalObjectTypeException;
use Neos\Flow\Persistence\Exception\UnknownObjectException;
use Psr\Log\LoggerInterface;

class TaskRunner
{
    /**
     * @Flow\Inject
     * @var TaskExecutionRepository
     */
    protected $taskExecutionRepository;

    /**
     * @Flow\Inject
     * @var PendingExecutionFinder
     */
    protected $executionFinder;

    /**
     * @Flow\Inject
     * @var PersistenceManager
     */
    protected $persistenceManager;

    /**
     * @Flow\Inject
     * @var TaskHandlerFactory
     */
    protected $taskHandlerFactory;

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function runTasks(): void
    {
        foreach ($this->executionFinder->findNext() as $execution) {
            try {
                $this->run($execution);
            } catch (TaskExitException $exception) {
                return;
            }
        }
    }

    /**
     * @param TaskExecution $execution
     * @throws TaskExitException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    private function run(TaskExecution $execution): void
    {
        $startTime = microtime(true);
        $execution->setStartTime(new DateTime());
        $execution->setStatus(TaskStatus::RUNNING);
        $this->taskExecutionRepository->update($execution);

        try {
            $execution = $this->hasPassed($execution, $this->handle($execution));
        } catch (TaskExitException $exception) {
            throw $exception;
        } catch (Exception $exception) {
            $execution = $this->hasFailed($execution, $exception);
        } finally {
            $this->finalize($execution, $startTime);
        }
    }

    /**
     * Handle given execution and fire before and after events.
     *
     * @throws Exception
     */
    private function handle(TaskExecution $execution): string
    {
        try {
            return $this->execute($execution);
        } catch (TaskRetryException $exception) {
            // this find is necessary because the storage could be
            // invalid (clear in doctrine) after handling an execution.
            $this->reFetchExecution($execution);

            if ($execution->getAttempts() === $exception->getMaximumAttempts()) {
                throw $exception->getPrevious();
            }

            $execution->reset()->incrementAttempts();

            $this->taskExecutionRepository->update($execution);

            throw new TaskExitException();
        }
    }

    public function execute(TaskExecution $execution): string
    {
        $handler = $this->taskHandlerFactory->get($execution->getHandlerClass());

        try {
            $this->logger->info(sprintf('Start running task %s', $execution->getTaskIdentifier()), LogEnvironment::fromMethodName(__METHOD__));
            return $handler->handle($execution->getWorkload());
        } catch (TaskFailedException $exception) {
            $this->logger->error(sprintf('Task %s failed with exception "%s"', $execution->getTaskIdentifier(), $exception->getPrevious()->getMessage()), LogEnvironment::fromMethodName(__METHOD__));
            throw $exception->getPrevious();
        } catch (Exception $exception) {
            if (!$handler instanceof TaskRetryException) {
                $this->logger->error(sprintf('Task %s failed with exception "%s"', $execution->getTaskIdentifier(), $exception->getMessage()), LogEnvironment::fromMethodName(__METHOD__));
                throw $exception;
            }

            $this->logger->warning(sprintf('Restarting Task %s, after failing with exception "%s"', $execution->getTaskIdentifier(), $exception->getMessage()), LogEnvironment::fromMethodName(__METHOD__));
            throw new TaskRetryException($handler->getMaximumAttempts(), $exception);
        }
    }

    /**
     * The given task passed the run.
     */
    private function hasPassed(TaskExecution $execution, $result)
    {
        $this->reFetchExecution($execution);
        $execution->setStatus(TaskStatus::COMPLETED);
        $execution->setResult($result);

        return $execution;
    }

    private function hasFailed(TaskExecution $execution, Exception $exception): TaskExecution
    {
        $this->reFetchExecution($execution);
        $execution->setException($exception->__toString());
        $execution->setStatus(TaskStatus::FAILED);

        return $execution;
    }

    private function finalize(TaskExecution $execution, float $startTime): void
    {
        $this->reFetchExecution($execution);
        if ($execution->getStatus() !== TaskStatus::PLANNED) {
            $execution->setEndTime(new DateTime());
            $execution->setDuration(microtime(true) - $startTime);
        }

        $this->taskExecutionRepository->update($execution);
    }

    /**
     * This find is necessary because the storage could be
     * invalid (clear in doctrine) after handling an execution.
     *
     * @param TaskExecution $execution
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws \Neos\Flow\Persistence\Exception
     */
    private function reFetchExecution(TaskExecution $execution): void
    {
        $this->persistenceManager->persistAll();
        $execution = $this->taskExecutionRepository->findByIdentifier($this->persistenceManager->getIdentifierByObject($execution));
    }
}
