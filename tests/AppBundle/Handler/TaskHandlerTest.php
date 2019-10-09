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

    public function setUp()
    {
        $this->mockEntityManager = $this->createMock(EntityManagerInterface::class);
        $this->taskHandler = new TaskHandler($this->mockEntityManager);
    }

    /**
     * @dataProvider getTasks
     * @param $title
     * @param $content
     * @param $username
     * @throws \Exception
     */
    public function testCreateTaskWithValidData($title, $content, $username)
    {
        $this->mockEntityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Task::class));
        $this->mockEntityManager->expects($this->atLeastOnce())->method('flush');

        $data = [
            'title' => $title,
            'content' => $content
        ];

        $user = new User();
        $user->setUsername($username);

        $task = $this->taskHandler->create($data, $user);
        $this->assertInstanceOf(Task::class, $task);
        $this->assertInstanceOf(User::class, $task->getAuthor());
        $this->assertInternalType('string', $task->getTitle());
        $this->assertInternalType('string', $task->getContent());
        $this->assertSame($title, $task->getTitle(), "title don't match");
        $this->assertSame($content, $task->getContent(), "content don't match");
        $this->assertSame($username, $task->getAuthor()->getUsername(), "username don't match");
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return [
            'Task #1' => ['Homework', 'Do my homework', 'Bob'],
            'Task #2' => ['Swimming', 'Go to the swimming pool', 'Stacy']
        ];
    }
}
