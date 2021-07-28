<?php
declare(strict_types=1);

namespace Flowpack\Task\Exceptions;

/**
 * Will be thrown by RetryTaskHandler to indicate that the current run was failed and should not be retried.
 */
class TaskFailedException extends \Exception
{
    public function __construct(\Exception $previous)
    {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);
    }
}
