<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;


use Cron\CronExpression;

interface TaskInterface
{
    public function getLabel(): string;

    public function getHandlerClass(): string;

    public function getFirstExecution(): ?\DateTime;

    public function getLastExecution(): ?\DateTime;

    public function getCronExpression(): ?CronExpression;

    public function getIdentifier(): string;
}
