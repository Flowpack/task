<?php
declare(strict_types=1);

namespace Flowpack\Task\Exceptions;

/**
 * Internal exception to indicate a retry for given exception.
 */
class TaskRetryException extends \Exception
{
    private int $maximumAttempts;

    public function __construct(int $maximumAttempts, \Exception $previous)
    {
        parent::__construct($previous->getMessage(), $previous->getCode(), $previous);

        $this->maximumAttempts = $maximumAttempts;
    }

    public function getMaximumAttempts(): int
    {
        return $this->maximumAttempts;
    }
}
