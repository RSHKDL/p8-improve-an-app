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
     * @dataProvider voterProvider
     * @param User|null $userLoggedIn
     * @param User|null $userToCheckAgainst
     * @param int $expected
     */
    public function testVote(?User $userLoggedIn, ?User $userToCheckAgainst, $expected)
    {
        $token = new AnonymousToken('secret', 'anonymous');

        if ($userLoggedIn) {
            $token = new UsernamePasswordToken($userLoggedIn, 'credentials', 'memory');
        }

        $userVoter = new UserVoter();
        $result = $userVoter->vote($token, $userToCheckAgainst, [UserVoter::CAN_EDIT]);

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function voterProvider()
    {
        $user = $this->createMockUser(true, false);
        $otherUser = $this->createMockUser(false, false);
        $adminUser = $this->createMockUser(false, true);

        return [
            // $userLoggedIn, $userToCheckAgainst, $expected
            '#1 same user' => [$user, $user, VoterInterface::ACCESS_GRANTED],
            '#2 other user' => [$otherUser, $user, VoterInterface::ACCESS_DENIED],
            '#3 other user is admin' => [$adminUser, $user, VoterInterface::ACCESS_GRANTED],
            '#4 no user logged in' => [null, $user, VoterInterface::ACCESS_DENIED],
        ];
    }

    /**
     * @param bool $isSame
     * @param bool $isAdmin
     * @return \PHPUnit\Framework\MockObject\MockObject
     */
    private function createMockUser($isSame, $isAdmin)
    {
        $user = $this->createMock(User::class);
        if ($isSame) {
            $user->method('getId')->willReturn(1);
        } else {
            $user->method('getId')->willReturn(2);
        }
        if ($isAdmin) {
            $user->method('getRoles')->willReturn([User::ROLE_ADMIN]);
        } else {
            $user->method('getRoles')->willReturn([User::ROLE_USER]);
        }

        return $user;
    }
}
