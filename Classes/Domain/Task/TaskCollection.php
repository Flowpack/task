<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

use Doctrine\Common\Collections\ArrayCollection;

class TaskCollection extends ArrayCollection
{
    public function getTask(string $taskIdentifier): TaskInterface
    {
        $task = $this->get($taskIdentifier);
        if ($task === null) {
            throw new \InvalidArgumentException(sprintf('Task "%s" does not exist in this collection', $taskIdentifier), 1645610446);
        }
        return $task;
    }

    public function filterEndBeforeNow(): ArrayCollection
    {
        return $this->filter(static function (Task $task) {
            return $task->getLastExecution() === null || $task->getLastExecution() > new \DateTime();
        });
    }
}
