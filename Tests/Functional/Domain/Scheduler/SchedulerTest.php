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
use Flowpack\Task\Tests\Functional\Helper\TaskCollectionConfigurationHelper;
use Neos\Flow\Tests\FunctionalTestCase;

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
        TaskCollectionConfigurationHelper::prepareTaskCollectionFactoryWithConfiguration(
            $this->taskCollectionFactory,
            [
                'singleTask' => [
                    'handlerClass' => TestHandler::class,
                ]
            ]
        );

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
        TaskCollectionConfigurationHelper::prepareTaskCollectionFactoryWithConfiguration(
            $this->taskCollectionFactory,
            [
                'taskInThePast' => [
                    'handlerClass' => TestHandler::class,
                    'lastExecution' => '2021-01-01',
                ]
            ]
        );

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
        TaskCollectionConfigurationHelper::prepareTaskCollectionFactoryWithConfiguration(
            $this->taskCollectionFactory,
            [
                'taskInThePast' => [
                    'handlerClass' => TestHandler::class,
                    'cronExpression' => '0 0 * * *',
                ]
            ]
        );

        $this->scheduler->scheduleTasks();
        $this->persistenceManager->persistAll();

        $taskExecutions = $this->taskExecutionRepository->findAll();
        self::assertEquals(1, $taskExecutions->count());

        /** @var TaskExecution $taskExecution */
        $taskExecution = $taskExecutions->getFirst();
        self::assertEquals($taskExecution->getScheduleTime(), (new DateTime())->modify('+1 day')->setTime(0, 0, 0));
    }

    /**
     * @test
     */
    public function firstExecutionOverridesCron(): void
    {
        $effectiveFirstExecution = (new DateTime('now'))->setTime(8, 0, 0)->add(new \DateInterval('P1D'));

        TaskCollectionConfigurationHelper::prepareTaskCollectionFactoryWithConfiguration(
            $this->taskCollectionFactory,
            [
                'taskInThePast' => [
                    'handlerClass' => TestHandler::class,
                    'cronExpression' => '* * * * *',
                    'firstExecution' => $effectiveFirstExecution->format('Y-m-d H:i:0')
                ]
            ]
        );

        $this->scheduler->scheduleTasks();
        $this->persistenceManager->persistAll();

        $taskExecutions = $this->taskExecutionRepository->findAll();
        self::assertEquals(1, $taskExecutions->count());

        /** @var TaskExecution $taskExecution */
        $taskExecution = $taskExecutions->getFirst();
        self::assertEquals($taskExecution->getScheduleTime(), $effectiveFirstExecution);
    }
}
