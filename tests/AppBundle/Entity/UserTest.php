<?php

namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserTest
 * @author ereshkidal
 */
class UserTest extends TestCase
{
    public function testAdminUser()
    {
        $user = new User();
        $user->setRoles([User::ROLE_ADMIN]);

        $this->assertInstanceOf(UserInterface::class, $user);
        $this->assertInternalType('bool', $user->isAdmin());
        $this->assertTrue($user->isAdmin());
    }

    public function testAddAndRemoveTasksToUser()
    {
        $user = new User();
        $task1 = $this->createMock(Task::class);
        $task1->method('getId')->willReturn(1);
        $task2 = $this->createMock(Task::class);
        $task2->method('getId')->willReturn(2);

        $user->addTask($task1)->addTask($task2);
        $this->assertContains($task1, $user->getTasks());
        $this->assertContains($task2, $user->getTasks());

        $user->removeTask($task2);
        $this->assertContains($task1, $user->getTasks());
        $this->assertNotContains($task2, $user->getTasks());
    }
}
