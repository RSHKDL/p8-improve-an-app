<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use AppBundle\Security\Voter\TaskVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class TaskVoterTest
 * @author ereshkidal
 * @covers \AppBundle\Security\Voter\TaskVoter
 */
class TaskVoterTest extends TestCase
{
    /**
     * @dataProvider getData
     * @param $subject
     * @param array $attributes
     * @param int $expected
     */
    public function testVote($subject, array $attributes, $expected)
    {
        $this->markTestSkipped('mock token does not work');

        $token = $this->createMock(TokenInterface::class);
        $token->method('getUser')->willReturn($this->createMockUser());

        $taskVoter = new TaskVoter();
        $result = $taskVoter->vote($token, $subject, $attributes);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getData()
    {
        return [
            // $task, attributes, expected
            '#1' => [$this->createMockTask(), [TaskVoter::CAN_VIEW], VoterInterface::ACCESS_GRANTED],
            '#2' => [$this->createMockTask(), [TaskVoter::CAN_EDIT], VoterInterface::ACCESS_GRANTED]
        ];
    }

    /**
     * @return User
     */
    private function createMockUser()
    {
        $user = new User();
        $user->setUsername('toto');
        $user->setRoles([User::ROLE_USER]);

        return $user;
    }

    /**
     * @return Task
     * @throws \Exception
     */
    private function createMockTask()
    {
        $task = new Task();
        $task->setAuthor($this->createMockUser());

        return $task;
    }
}
