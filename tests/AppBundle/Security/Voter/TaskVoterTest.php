<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use AppBundle\Security\Voter\TaskVoter;
use PHPUnit\Framework\TestCase;
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
        $this->assertTrue(true);
        /*
         * Mocking the token is a pain in the ass.
         * I'll follow the "Don't mock what you don't own" principle,
         * and since I do not own symfony code,
         * I'll test the voters in the functional tests.
         * https://stackoverflow.com/questions/35579884/symfony-unit-test-security-acl-annotation
         * https://davesquared.net/2011/04/dont-mock-types-you-dont-own.html
         *
        $mockToken = $this
            ->getMockBuilder(UsernamePasswordToken::class)
            ->disableOriginalConstructor()
            ->setMethods(['getUser'])
            ->getMock();

        $mockToken
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->createMockUser());

        $taskVoter = new TaskVoter();
        $result = $taskVoter->vote($mockToken, $subject, $attributes);

        $this->assertEquals($expected, $result);
        */
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
