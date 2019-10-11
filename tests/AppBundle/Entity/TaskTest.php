<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TaskTest
 * @author ereshkidal
 */
class TaskTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testSetAuthor()
    {
        $task = new Task();
        $author = new User();
        $author->setUsername('Shakespeare');

        $task->setAuthor($author);
        $this->assertInstanceOf(UserInterface::class, $task->getAuthor());
        $this->assertSame('Shakespeare', $task->getAuthor()->getUsername());
    }

    /**
     * @throws \Exception
     */
    public function testIsDone()
    {
        $task = new Task();

        $this->assertFalse($task->isDone());
        $task->toggle(true);
        $this->assertTrue($task->isDone());
        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }

    /**
     * @throws \Exception
     */
    public function testSetCreatedAt()
    {
        $task = new Task();

        $this->assertInstanceOf(\DateTime::class, $task->getCreatedAt());
    }
}
