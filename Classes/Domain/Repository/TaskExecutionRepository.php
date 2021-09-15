<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Repository;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\AbstractQuery;
use Flowpack\Task\Domain\Model\TaskExecution;
use Neos\Flow\Annotations as Flow;
use Flowpack\Task\Domain\Task\Task;
use Flowpack\Task\Domain\Task\TaskStatus;
use Neos\Flow\Persistence\Doctrine\Repository;
use Neos\Flow\Persistence\QueryInterface;
use Neos\Flow\Persistence\QueryResultInterface;

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
                    $query->equals('status', TaskStatus::PLANNED),
                    $query->equals('status', TaskStatus::RUNNING),
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

    public function removePlannedTask(Task $task): void
    {
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('taskIdentifier', $task->getIdentifier()),
                $query->equals('status', TaskStatus::PLANNED)
            )
        );

        foreach ($query->execute() as $scheduledTask) {
            $this->remove($scheduledTask);
        }
    }

    public function findLatest(Task $task, int $limit = 5): QueryResultInterface
    {
        $query = $this->createQuery();

        $query->matching(
            $query->logicalAnd(
                $query->equals('taskIdentifier', $task->getIdentifier()),
                $query->logicalNot(
                    $query->equals('status', TaskStatus::PLANNED)
                )
            )
        )->setOrderings(['scheduleTime' => QueryInterface::ORDER_DESCENDING]);

        return $query->execute();
    }

    public function findNextScheduled(DateTime $runTime, array $skippedExecutions = [], Task $task = null): ?TaskExecution
    {
        $queryBuilder = $this->createQueryBuilder('taskExecution');

        $queryBuilder
            ->where($queryBuilder->expr()->lte('taskExecution.scheduleTime', ':scheduleTime'))
            ->andWhere($queryBuilder->expr()->eq('taskExecution.status', ':status'))
            ->orderBy('taskExecution.scheduleTime', QueryInterface::ORDER_DESCENDING)
            ->setMaxResults(1)
            ->setParameter('scheduleTime', $runTime, Types::DATETIME_MUTABLE)
            ->setParameter('status', TaskStatus::PLANNED);

        if (!empty($skippedExecutions)) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->not($queryBuilder->expr()->in('taskExecution.Persistence_Object_Identifier', ':skippedExecutions'))
            )->setParameter('skippedExecutions', $skippedExecutions);
        }

        if ($task !== null) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->eq('taskExecution.taskIdentifier', ':taskIdentifier'))
                ->setParameter('taskIdentifier', $task->getIdentifier());
        }

        return $queryBuilder->getQuery()->getOneOrNullResult(AbstractQuery::HYDRATE_OBJECT);
    }
}
