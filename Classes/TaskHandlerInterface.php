<?php
declare(strict_types=1);

namespace Flowpack\Task;

interface TaskHandlerInterface
{

    public function handle(): void;

}
