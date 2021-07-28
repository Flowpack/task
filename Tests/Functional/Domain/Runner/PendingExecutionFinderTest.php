<?php
declare(strict_types=1);

namespace Flowpack\Task\Tests\Functional\Domain\Runner;

use Flowpack\Task\Domain\Repository\TaskExecutionRepository;
use Flowpack\Task\Domain\Runner\PendingExecutionFinder;
use Flowpack\Task\Domain\Scheduler\Scheduler;
use Flowpack\Task\Domain\Task\TaskCollectionFactory;
use Flowpack\Task\Tests\Functional\Fixture\TestHandler;
use Flowpack\Task\Tests\Functional\Helper\TaskCollectionConfigurationHelper;
use Neos\Flow\Tests\FunctionalTestCase;

class PendingExecutionFinderTest extends FunctionalTestCase
{

    protected static $testablePersistenceEnabled = true;

    protected ?Scheduler $scheduler;

    protected ?TaskCollectionFactory $taskCollectionFactory;

    protected ?TaskExecutionRepository $taskExecutionRepository;

    protected ?PendingExecutionFinder $pendingExecutionFinder;

    public function setUp(): void
    {
        parent::setUp();

        $this->taskCollectionFactory = $this->objectManager->get(TaskCollectionFactory::class);
        $this->taskExecutionRepository = $this->objectManager->get(TaskExecutionRepository::class);
        $this->pendingExecutionFinder = $this->objectManager->get(PendingExecutionFinder::class);
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
                    'cronExpression' => '0 0 * * *',

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

        $nextExecution = $this->pendingExecutionFinder->findNext();

    }
}
