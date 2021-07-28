<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;


interface TaskInterface
{
    public function getLabel(): string;

    public function getHandlerClass(): string;

    public function getFirstExecution(): ?\DateTime;

    public function getLastExecution(): ?\DateTime;
}
