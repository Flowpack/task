<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

use Cron\CronExpression;

class Task implements TaskInterface
{
    protected string $identifier;

    protected string $handlerClass;

    protected string $label = '';

    protected string $description = '';

    protected ?CronExpression $cronExpression;

    protected ?WorkloadInterface $workload = null;

    protected ?\DateTime $firstExecution;

    protected ?\DateTime $lastExecution;

    public function __construct(
        string $identifier,
        ?CronExpression $cronExpression,
        string $handlerClass,
        string $label,
        string $description = '',
        $workload = null,
        \DateTime $firstExecution = null,
        \DateTime $lastExecution = null
    )
    {
        $this->identifier = $identifier;
        $this->handlerClass = $handlerClass;
        $this->label = $label;
        $this->description = $description;
        $this->workload = $workload;
        $this->cronExpression = $cronExpression;

        $this->firstExecution = $firstExecution;
        $this->lastExecution = $lastExecution;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getHandlerClass(): string
    {
        return $this->handlerClass;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCronExpression(): ?CronExpression
    {
        return $this->cronExpression;
    }

    public function getWorkload(): ?Workload
    {
        return $this->workload;
    }

    public function getFirstExecution(): ?\DateTime
    {
        return $this->firstExecution;
    }

    public function getLastExecution(): ?\DateTime
    {
        return $this->lastExecution;
    }
}
