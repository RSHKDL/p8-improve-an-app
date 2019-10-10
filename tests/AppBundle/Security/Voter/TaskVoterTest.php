<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use AppBundle\Security\Voter\TaskVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class TaskVoterTest
 * @author ereshkidal
 * @covers \AppBundle\Security\Voter\TaskVoter
 */
class TaskVoterTest extends TestCase
{
    /**
     * @dataProvider voterProvider
     * @param User|null $user
     * @param bool $isAuthor
     * @param int $expected
     * @throws \Exception
     */
    public function testVote(?User $user, $isAuthor, $expected)
    {
        /*
         * Mocking the token is a pain in the ass.
         * I'll follow the "Don't mock what you don't own" principle,
         * and since I do not own symfony code,
         * I'll test the voters in the functional tests.
         * https://stackoverflow.com/questions/35579884/symfony-unit-test-security-acl-annotation
         * https://davesquared.net/2011/04/dont-mock-types-you-dont-own.html
         */
        $task = new Task();
        $token = new AnonymousToken('secret', 'anonymous');

        if ($user) {
            $token = new UsernamePasswordToken($user, 'credentials', 'memory');
        }

        if ($user && $isAuthor) {
            $task->setAuthor($user);
        }

        $taskVoter = new TaskVoter();
        $result = $taskVoter->vote($token, $task, [TaskVoter::CAN_EDIT]);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function voterProvider()
    {
        return [
            // $user, isAuthor, expected
            '#1 same user' => [$this->createMockUser(), true, VoterInterface::ACCESS_GRANTED],
            '#2 other user' => [$this->createMockUser(), false, VoterInterface::ACCESS_DENIED],
            '#3 no user' => [null, false, VoterInterface::ACCESS_DENIED]
        ];
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createMockUser()
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getRoles')->willReturn([User::ROLE_USER]);

        return $user;
    }
}
