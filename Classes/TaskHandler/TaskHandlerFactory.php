<?php
declare(strict_types=1);

namespace Flowpack\Task\TaskHandler;

use Flowpack\Task\Exceptions\InvalidTaskHandlerException;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Configuration\Exception\InvalidConfigurationTypeException;
use Neos\Flow\ObjectManagement\Exception\CannotBuildObjectException;
use Neos\Flow\ObjectManagement\Exception\UnknownObjectException;
use Neos\Flow\ObjectManagement\ObjectManager;

/**
 * @Flow\Scope("singleton")
 */
class TaskHandlerFactory
{

    /**
     * @Flow\Inject
     * @var ObjectManager
     */
    protected $objectManager;


    /**
     * @param string $taskHandlerClassName
     * @return TaskHandlerInterface
     * @throws InvalidTaskHandlerException
     * @throws InvalidConfigurationTypeException
     * @throws CannotBuildObjectException
     * @throws UnknownObjectException
     */
    public function get(string $taskHandlerClassName): TaskHandlerInterface
    {
        if (!class_exists($taskHandlerClassName)) {
            throw new InvalidTaskHandlerException(sprintf('No taskHandler with class "%s" was found', $taskHandlerClassName), 1627476970);
        }

        $taskHandler = $this->objectManager->get($taskHandlerClassName);

        if (!$taskHandler instanceof TaskHandlerInterface) {
            throw new InvalidTaskHandlerException(sprintf('The taskHandler class "%s" is not of type "%s"', $taskHandlerClassName, TaskHandlerInterface::class), 1627477053);
        }

        return $taskHandler;
    }
}
