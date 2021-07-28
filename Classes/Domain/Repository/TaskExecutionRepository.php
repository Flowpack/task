<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Flowpack\Task\Domain\Task\Task;
use Flowpack\Task\Domain\Task\TaskStatus;
use Neos\Flow\Persistence\QueryResultInterface;
use Neos\Flow\Persistence\Repository;

/**
 * @Flow\Scope("singleton")
 */
class TaskExecutionRepository extends Repository
{
    public function findPending(Task $task): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('taskIdentifier', $task->getIdentifier()),
                $query->logicalOr(
                    $query->contains('status', TaskStatus::PLANNED),
                    $query->contains('status', TaskStatus::RUNNING),
                )
            )
        );
        return $query->execute();
    }

    public function findByTask(Task $task): QueryResultInterface
    {
        $query = $this->createQuery();
        $query->matching(
            $query->equals('taskIdentifier', $task->getIdentifier()),
        );
        return $query->execute();
    }
}
