<?php
declare(strict_types=1);

namespace Flowpack\Task\Tests\Functional\Helper;

use Flowpack\Task\Domain\Task\TaskCollectionFactory;
use Neos\Utility\ObjectAccess;

class TaskCollectionConfigurationHelper
{
    public static function prepareTaskCollectionFactoryWithConfiguration(TaskCollectionFactory $taskCollectionFactory, array $taskConfigurations): void
    {
        ObjectAccess::setProperty($taskCollectionFactory, 'taskConfigurations', $taskConfigurations, true);
        ObjectAccess::setProperty($taskCollectionFactory, 'taskCollection', null, true);
    }
}
