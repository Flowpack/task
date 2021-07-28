<?php
declare(strict_types=1);

namespace Flowpack\Task\Tests\Functional\Domain\Repository;

use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Flowpack\Task\Domain\Scheduler\Scheduler;
use Flowpack\Task\Domain\Task\TaskCollectionFactory;
use Flowpack\Task\Tests\Functional\Fixture\TestHandler;
use Flowpack\Task\Tests\Functional\Helper\TaskCollectionConfigurationHelper;
use Neos\Flow\Tests\FunctionalTestCase;

class TaskExecutionRepositoryTest extends FunctionalTestCase
{

    protected static $testablePersistenceEnabled = true;

    protected ?Scheduler $scheduler;

    protected ?TaskCollectionFactory $taskCollectionFactory;

    protected ?TaskExecutionRepository $taskExecutionRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->taskCollectionFactory = $this->objectManager->get(TaskCollectionFactory::class);
        $this->taskExecutionRepository = $this->objectManager->get(TaskExecutionRepository::class);
        $this->scheduler = $this->objectManager->get(Scheduler::class);
    }

    /**
     * @test
     */
    public function findNext(): void
    {
        TaskCollectionConfigurationHelper::prepareTaskCollectionFactoryWithConfiguration(
            $this->taskCollectionFactory,
            [
                'taskOne' => [
                    'handlerClass' => TestHandler::class,
                    'cronExpression' => '5 * * * *',

                ],
                'taskTwo' => [
                    'handlerClass' => TestHandler::class,
                    'cronExpression' => '* * * * *',

                ],
                'taskThree' => [
                    'handlerClass' => TestHandler::class,
                    'cronExpression' => '* * * * *',
                    'firstExecution' => '2100-01-01 00:00:00',
                ]
            ]
        );

        $this->scheduler->scheduleTasks();
        $this->persistenceManager->persistAll();

        $runTime = (new \DateTime())->add(new \DateInterval('PT8H'));

        $nextExecution = $this->taskExecutionRepository->findNextScheduled($runTime);
        self::assertNotNull($nextExecution, 'No execution found');

        self::assertEquals('taskTwo', $nextExecution->getTaskIdentifier(), 'First one should be taskTwo');

        $nextExecutionExcluded = $this->taskExecutionRepository->findNextScheduled($runTime, [$nextExecution]);
        self::assertNotNull($nextExecutionExcluded, 'No execution found');

        self::assertEquals('taskOne', $nextExecutionExcluded->getTaskIdentifier());
    }

}
