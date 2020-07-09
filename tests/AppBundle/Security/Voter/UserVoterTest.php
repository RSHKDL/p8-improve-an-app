<?php

namespace Tests\AppBundle\Security\Voter;

use AppBundle\Entity\User;
use AppBundle\Security\Voter\UserVoter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * Class UserVoterTest
 * @author ereshkidal
 */
class UserVoterTest extends TestCase
{
    /**
     * @dataProvider provideCases
     * @param string $attribute
     * @param User|null $userLoggedIn
     * @param User|null $userToCheckAgainst
     * @param int $expectedVote
     */
    public function testVote(string $attribute, ?User $userLoggedIn, ?User $userToCheckAgainst, int $expectedVote): void
    {
        $token = new AnonymousToken('secret', 'anonymous');
        if ($userLoggedIn) {
            $token = new UsernamePasswordToken($userLoggedIn, 'credentials', 'memory');
        }

        $userVoter = new UserVoter();
        $this->assertEquals($expectedVote, $userVoter->vote($token, $userToCheckAgainst, [$attribute]));
    }

    /**
     * Attribute, UserLoggedIn, UserToCheckAgainst, expectedVote
     * @return \Generator
     */
    public function provideCases(): \Generator
    {
        $user = $this->createMockUser(true, false);
        $otherUser = $this->createMockUser(false, false);
        $adminUser = $this->createMockUser(false, true);

        yield '#1 User can edit self' => [
            'edit',
            $user,
            $user,
            VoterInterface::ACCESS_GRANTED
        ];

        yield '#2 User cannot view other users' => [
            'view',
            $user,
            $otherUser,
            VoterInterface::ACCESS_DENIED
        ];

        yield '#3 User cannot edit other users' => [
            'edit',
            $user,
            $otherUser,
            VoterInterface::ACCESS_DENIED
        ];

        yield '#4 Admin can edit other users' => [
            'edit',
            $adminUser,
            $otherUser,
            VoterInterface::ACCESS_GRANTED
        ];

        yield '#5 Anonymous cannot view other users' => [
            'view',
            null,
            $otherUser,
            VoterInterface::ACCESS_DENIED
        ];

        yield '#6 Anonymous cannot edit other users' => [
            'edit',
            null,
            $otherUser,
            VoterInterface::ACCESS_DENIED
        ];

        yield '#7 Vote with wrong attribute' => [
            'wrong',
            $user,
            $user,
            VoterInterface::ACCESS_ABSTAIN
        ];

        yield '#8 Admin cannot delete self' => [
            'delete',
            $adminUser,
            $adminUser,
            VoterInterface::ACCESS_DENIED
        ];
    }

    private function createMockUser(bool $isSame, bool $isAdmin): User
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn($isSame ? 1 : 2);
        $user->method('getRoles')->willReturn([$isAdmin ? User::ROLE_ADMIN : User::ROLE_USER]);

        return $user;
    }
}
