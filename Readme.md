# Flow Framework Task Scheduler

This package provides a simple to use task scheduler for Neos Flow. Tasks are configured via settings, recurring tasks can be configured using cron syntax. Detailed options configure the first and last executions as well as options for the class handling the task. 

Schedulling and runnign tasks are decoupled: The `Scheduler` schedules tasks whcih the are executed by the `TaskRunner`. This architecture allows to receive and display metrics of already executed tasks.

Most of the architectural ideas behind the package are taken from [php-task](https://github.com/php-task/php-task), and reimplemented for Neos Flow.

## Installation

```
composer require 'flowpack/task'
```

## Configuration

### Defining A Task

```yaml
Flowpack:
  Task:
    tasks:
      'a-unique-identifier':
        label: The label of this task
        description: Some detailed description of thsi task
        # A class, implementing the TaskHandlerInterface  
        handlerClass: 'Vendor\Package\TaskHandler\TaskHandlerClass'
        cronExpression: '*/5 * * * *'
        # A workload, eg. some configuration, given to the taskHandler
        workload:
          interval: PT5M
```

### General Options

`lockStorage`: Configuration string for the lock storage used for taksHandler implementing `LockingTaskHandlerInterface`. See https://symfony.com/doc/current/components/lock.html#available-stores for more options

## Implementing A Task Handler

A task handler contais the code executed for a specific task. Your command handler has to implement one of the following interfaces:

`Flowpack\Task\TaskHandler\TaskHandlerInterface`

A basic task. The interface requires the method `handle(WorkloadInterface $workload): string` to be implemented. The return value serves as information for successfully executed tasks. 

`Flowpack\Task\TaskHandler\RetryTaskHandlerInterface`

Also requires `getMaximumAttempts(): int` to be implemented. Allowing the tasks to be retried on failure.

`Flowpack\Task\TaskHandler\LockingTaskHandlerInterface`

Also requires `getLockIdentifier(WorkloadInterface $workload): string` to be implemented. The return value specifies a lock to be aquired. When such a task is running, other tasks requiring the same lock will be skipped.

## Available Commands

Show a list of all defined and scheduled tasks:

	./flow task:list
	
Show details about a specific task:

	./flow task:show <taskIdentifier>
	
