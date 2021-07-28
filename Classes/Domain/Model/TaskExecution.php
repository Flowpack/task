<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Model;

use Flowpack\Task\Domain\Task\Task;
use Flowpack\Task\Domain\Task\TaskStatus;
use Flowpack\Task\Domain\Task\Workload;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class TaskExecution
{
    /**
     * @var string
     */
    protected $taskIdentifier;

    /**
     * @ORM\Column(name="workload", type="object")
     * @var Workload
     */
    protected $workload;

    /**
     * @var string
     */
    protected $handlerClass = '';

    /**
     * @var \DateTime
     */
    protected $scheduleTime;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $startTime;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $endTime;

    /**
     * @ORM\Column(nullable=true)
     * @var float
     */
    protected $duration;

    /**
     * @var string
     */
    protected $status = TaskStatus::PLANNED;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $result;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $exception;

    /**
     * @var int
     */
    protected $attempts = 1;

    public function __construct(Task $task, \DateTime $scheduleTime)
    {
        $this->taskIdentifier = $task->getIdentifier();
        $this->workload = $task->getWorkload();
        $this->handlerClass = $task->getHandlerClass();
        $this->scheduleTime = $scheduleTime;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return TaskExecution
     */
    public function setStatus(string $status): TaskExecution
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getTaskIdentifier(): string
    {
        return $this->taskIdentifier;
    }

    /**
     * @return Workload
     */
    public function getWorkload(): ?Workload
    {
        return $this->workload;
    }

    /**
     * @return string
     */
    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    /**
     * @return \DateTime
     */
    public function getScheduleTime(): \DateTime
    {
        return $this->scheduleTime;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime(): \DateTime
    {
        return $this->startTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime(): \DateTime
    {
        return $this->endTime;
    }

    /**
     * @return float
     */
    public function getDuration(): float
    {
        return $this->duration;
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }

    /**
     * @return string
     */
    public function getException(): string
    {
        return $this->exception;
    }

    /**
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }
}
