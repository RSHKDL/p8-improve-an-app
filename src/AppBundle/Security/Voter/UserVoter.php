<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class UserVoter
 * @author ereshkidal
 */
final class UserVoter extends Voter
{
    public const CAN_VIEW = 'view';
    public const CAN_EDIT = 'edit';
    public const CAN_DELETE = 'delete';

    /**
     * @param string $attribute
     * @param mixed $subject
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::CAN_VIEW, self::CAN_EDIT, self::CAN_DELETE], true)
            && $subject instanceof User;
    }

    /**
     * @param string $attribute
     * @param mixed $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $currentUser */
        $currentUser = $token->getUser();
        if (!$currentUser instanceof UserInterface) {
            return false;
        }

        /** @var User $user */
        $user = $subject;

        switch ($attribute) {
            case self::CAN_EDIT:
                return $this->canEdit($currentUser, $user);
                break;
            case self::CAN_VIEW:
                return $this->canView($currentUser, $user);
                break;
            case self::CAN_DELETE:
                return $this->canDelete($currentUser, $user);
                break;
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('This code should not be reached!');
        // @codeCoverageIgnoreEnd
    }

    private function canEdit(User $currentUser, User $user): bool
    {
        return $currentUser === $user || in_array(User::ROLE_ADMIN, $currentUser->getRoles(), true);
    }

    private function canView(User $currentUser, User $user): bool
    {
        return $this->canEdit($currentUser, $user);
    }

    private function canDelete(User $currentUser, User $user): bool
    {
        return $currentUser !== $user && in_array(User::ROLE_ADMIN, $currentUser->getRoles(), true);
    }
}
