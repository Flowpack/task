<?php
declare(strict_types=1);

namespace Flowpack\Task\Tests\Functional\Domain\Scheduler;

use DateTime;
use Exception;
use Flowpack\Task\Domain\Model\TaskExecution;
use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Flowpack\Task\Domain\Scheduler\Scheduler;
use Flowpack\Task\Domain\Task\TaskCollectionFactory;
use Flowpack\Task\Tests\Functional\Fixture\TestHandler;
use Neos\Flow\Tests\FunctionalTestCase;
use Neos\Utility\ObjectAccess;

class SchedulerTest extends FunctionalTestCase
{

    protected static $testablePersistenceEnabled = true;

    protected ?Scheduler $scheduler;

    protected ?TaskCollectionFactory $taskCollectionFactory;

    protected ?TaskExecutionRepository $taskExecutionRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->taskCollectionFactory = $this->objectManager->get(TaskCollectionFactory::class);
        $this->scheduler = $this->objectManager->get(Scheduler::class);
        $this->taskExecutionRepository = $this->objectManager->get(TaskExecutionRepository::class);
    }

    /**
     * @test
     * @throws Exception
     */
    public function taskGetsScheduledOnce(): void
    {
        $this->prepareTaskCollectionFactoryWithConfiguration([
            'singleTask' => [
                'handlerClass' => TestHandler::class,
            ]
        ]);

        $this->scheduler->scheduleTasks();
        $this->persistenceManager->persistAll();
        $this->scheduler->scheduleTasks();
        $this->persistenceManager->persistAll();

        $taskExecutions = $this->taskExecutionRepository->findAll();
        self::assertEquals(1, $taskExecutions->count());
    }

    /**
     * @test
     */
    public function taskIsNotScheduledIfLastExecutionIsInThePast(): void
    {
        $this->prepareTaskCollectionFactoryWithConfiguration([
            'taskInThePast' => [
                'handlerClass' => TestHandler::class,
                'lastExecution' => '2021-01-01',
            ]
        ]);

        $this->scheduler->scheduleTasks();
        $this->persistenceManager->persistAll();

        $taskExecutions = $this->taskExecutionRepository->findAll();
        self::assertEquals(0, $taskExecutions->count());
    }

    /**
     * @test
     */
    public function cronExpressionIsInterpreted(): void
    {
        $this->prepareTaskCollectionFactoryWithConfiguration([
            'taskInThePast' => [
                'handlerClass' => TestHandler::class,
                'cronExpression' => '0 0 * * *',
            ]
        ]);

        $this->scheduler->scheduleTasks();
        $this->persistenceManager->persistAll();

        $taskExecutions = $this->taskExecutionRepository->findAll();
        self::assertEquals(1, $taskExecutions->count());

        /** @var TaskExecution $taskExecution */
        $taskExecution = $taskExecutions->getFirst();
        self::assertEquals($taskExecution->getScheduleTime(), (new DateTime())->modify('+1 day')->setTime(0, 0, 0));
    }

    protected function prepareTaskCollectionFactoryWithConfiguration(array $taskConfigurations): void
    {
        ObjectAccess::setProperty($this->taskCollectionFactory, 'taskConfigurations', $taskConfigurations, true);
        ObjectAccess::setProperty($this->taskCollectionFactory, 'taskCollection', null, true);
    }

}
