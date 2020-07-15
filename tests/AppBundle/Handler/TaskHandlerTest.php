<?php

namespace Tests\AppBundle\Handler;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use AppBundle\Handler\TaskHandler;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * Class TaskHandlerTest
 * @author ereshkidal
 * @covers \AppBundle\Handler\TaskHandler
 */
class TaskHandlerTest extends TestCase
{
    /**
     * @var TaskHandler
     */
    private $taskHandler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $mockEntityManager;

    public function setUp(): void
    {
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->taskHandler = new TaskHandler($this->mockEntityManager);
    }

    /**
     * @dataProvider getTasks
     * @param Task $task
     * @param User $user
     */
    public function testCreateTaskWithValidData(Task $task, User $user): void
    {
        $this->mockEntityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Task::class));
        $this->mockEntityManager->expects($this->atLeastOnce())->method('flush');

        $task = $this->taskHandler->create($task, $user);
        $this->assertInstanceOf(User::class, $task->getAuthor());
        $this->assertIsString($task->getTitle());
        $this->assertIsString($task->getContent());
        $this->assertSame($user->getUsername(), $task->getAuthor()->getUsername());
    }

    /**
     * @return \Generator
     * @throws \Exception
     */
    public function getTasks(): \Generator
    {
        $user1 = new User();
        $user1->setUsername('Bob');
        $task1 = new Task();
        $task1->setTitle('Homework');
        $task1->setContent('Do my homework');

        $user2 = new User();
        $user2->setUsername('Stacy');
        $task2 = new Task();
        $task2->setTitle('Swimming');
        $task2->setContent('Go to the swimming pool');

        yield 'Task #1' => [
            $task1,
            $user1
        ];

        yield 'Task #2' => [
            $task2,
            $user2
        ];
    }
}
