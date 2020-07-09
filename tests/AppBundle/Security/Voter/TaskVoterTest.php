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
     * @dataProvider provideCases
     * @param string $attribute
     * @param User|null $user
     * @param bool $isAuthor
     * @param bool $isTaskAnonymous
     * @param int $expectedVote
     * @throws \Exception
     */
    public function testVote(string $attribute, ?User $user, bool $isAuthor, bool $isTaskAnonymous, int $expectedVote): void
    {
        $token = new AnonymousToken('secret', 'anonymous');
        if ($user) {
            $token = new UsernamePasswordToken($user, 'credentials', 'memory');
        }

        $task = new Task();
        if (!$isTaskAnonymous) {
            $task->setAuthor($isAuthor ? $user : $this->createMockUser(99));
        }

        $taskVoter = new TaskVoter();
        $vote = $taskVoter->vote($token, $task, [$attribute]);

        $this->assertEquals($expectedVote, $vote);
    }

    /**
     * Attributes, User, IsAuthor, IsTaskAnonymous, ExpectedVote
     * @throws \Exception
     */
    public function provideCases(): \Generator
    {
        yield '#1 anonymous user cannot view task' => [
            'view',
            null,
            false,
            false,
            VoterInterface::ACCESS_DENIED
        ];

        yield '#2 anonymous user cannot edit task' => [
            'edit',
            null,
            false,
            false,
            VoterInterface::ACCESS_DENIED
        ];

        yield '#3 non-owner can view task' => [
            'view',
            $this->createMockUser(),
            false,
            false,
            VoterInterface::ACCESS_GRANTED
        ];

        yield '#4 non-owner cannot edit task' => [
            'edit',
            $this->createMockUser(),
            false,
            false,
            VoterInterface::ACCESS_DENIED
        ];

        yield '#5 owner can edit owned task' => [
            'edit',
            $this->createMockUser(),
            true,
            false,
            VoterInterface::ACCESS_GRANTED
        ];

        yield '#6 admin can edit non owned task' => [
            'edit',
            $this->createMockUser(5, true),
            false,
            false,
            VoterInterface::ACCESS_GRANTED
        ];

        yield '#7 admin can edit anonymous task' => [
            'edit',
            $this->createMockUser(5, true),
            false,
            true,
            VoterInterface::ACCESS_GRANTED
        ];

        yield '#8 vote with wrong attribute' => [
            'wrong',
            $this->createMockUser(),
            false,
            false,
            VoterInterface::ACCESS_ABSTAIN
        ];
    }

    private function createMockUser(int $id = 1, bool $isAdmin = false): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($id);
        $user->method('getRoles')->willReturn([$isAdmin ? User::ROLE_ADMIN : User::ROLE_USER]);

        return $user;
    }
}
