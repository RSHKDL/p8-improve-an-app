<?php

namespace AppBundle\Security\Voter;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class TaskVoter
 * @author ereshkidal
 */
final class TaskVoter extends Voter
{
    public const CAN_VIEW = 'view';
    public const CAN_EDIT = 'edit';

    /**
     * @param string $attribute
     * @param Task $subject
     * @return bool
     */
    protected function supports($attribute, $subject): bool
    {
        return in_array($attribute, [self::CAN_VIEW, self::CAN_EDIT], true)
            && $subject instanceof Task;
    }

    /**
     * @param string $attribute
     * @param Task $subject
     * @param TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /** @var Task $task */
        $task = $subject;

        switch ($attribute) {
            case self::CAN_EDIT:
                return $this->canEdit($task, $user);
                break;
            case self::CAN_VIEW:
                return $this->canView();
                break;
        }

        // @codeCoverageIgnoreStart
        throw new \LogicException('This code should not be reached!');
        // @codeCoverageIgnoreEnd
    }

    private function canEdit(Task $task, User $user): bool
    {
        return $task->getAuthor() === $user || in_array(User::ROLE_ADMIN, $user->getRoles(), true);
    }

    private function canView(): bool
    {
        return true;
    }
}
