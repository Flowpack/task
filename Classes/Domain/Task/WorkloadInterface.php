<?php
declare(strict_types=1);

namespace Flowpack\Task\Domain\Task;

interface WorkloadInterface
{
    public function getData(): array;

}
