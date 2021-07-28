<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

use Doctrine\Common\Collections\ArrayCollection;

class TaskCollection extends ArrayCollection
{
    public function filterEndBeforeNow(): ArrayCollection
    {
        return $this->filter(static function (Task $task, $taskIdentifier) {
            return $task->getLastExecution() === null || $task->getLastExecution() > new \DateTime();
        });
    }
}
