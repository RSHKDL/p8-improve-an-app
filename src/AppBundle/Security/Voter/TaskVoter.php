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
    const CAN_VIEW = 'view';
    const CAN_EDIT = 'edit';

    /**
     * @param string $attribute
     * @param Task $subject
     * @return bool
     */
    protected function supports($attribute, $subject)
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
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
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
                return $this->canView($task, $user);
                break;
        }

        return false;
    }

    /**
     * @param Task $task
     * @param User $user
     * @return bool
     */
    private function canEdit(Task $task, User $user)
    {
        return $task->getAuthor() === $user || in_array(User::ROLE_ADMIN, $user->getRoles(), true);
    }

    /**
     * @param Task $task
     * @param User $user
     * @return bool
     */
    private function canView(Task $task, User $user)
    {
        return $this->canEdit($task, $user);
    }
}
