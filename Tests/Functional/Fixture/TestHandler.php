<?php
declare(strict_types=1);

namespace Flowpack\Task\Tests\Functional\Fixture;

use Flowpack\Task\TaskHandlerInterface;

class TestHandler implements TaskHandlerInterface
{

    public function handle(): void
    {
    }
}
