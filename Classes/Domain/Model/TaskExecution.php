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
     * @ORM\Column(nullable = true, type = "text")
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
     * @return Workload|null
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
     * @return \DateTime|null
     */
    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    /**
     * @return float|null
     */
    public function getDuration(): ?float
    {
        return $this->duration;
    }

    /**
     * @return string|null
     */
    public function getResult(): ?string
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

    /**
     * @param \DateTime $startTime
     * @return TaskExecution
     */
    public function setStartTime(\DateTime $startTime): TaskExecution
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * @param \DateTime $endTime
     * @return TaskExecution
     */
    public function setEndTime(\DateTime $endTime): TaskExecution
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * @param float $duration
     * @return TaskExecution
     */
    public function setDuration(float $duration): TaskExecution
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * @param string $result
     * @return TaskExecution
     */
    public function setResult(string $result): TaskExecution
    {
        $this->result = $result;
        return $this;
    }

    /**
     * @param string $exception
     * @return TaskExecution
     */
    public function setException(string $exception): TaskExecution
    {
        $this->exception = $exception;
        return $this;
    }

    /**
     * @param int $attempts
     * @return TaskExecution
     */
    public function setAttempts(int $attempts): TaskExecution
    {
        $this->attempts = $attempts;
        return $this;
    }

    public function reset(): TaskExecution
    {
        $this->startTime = null;
        $this->endTime = null;
        $this->result = null;
        $this->exception = null;
        $this->status = TaskStatus::PLANNED;

        return $this;
    }

    public function incrementAttempts(): TaskExecution
    {
        $this->attempts++;
        return $this;
    }
}
