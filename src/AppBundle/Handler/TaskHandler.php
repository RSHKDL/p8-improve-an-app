<?php

namespace AppBundle\Handler;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Could be improved by having one handler per action
 * with only one public handle method per class.
 *
 * Class TaskHandler
 * @author ereshkidal
 */
final class TaskHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * TaskHandler constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $data
     * @param User $user
     * @return Task
     * @throws \Exception
     */
    public function create(array $data, User $user): Task
    {
        $task = new Task();
        $task->setTitle($data['title']);
        $task->setContent($data['content']);
        $task->setAuthor($user);

        $this->entityManager->persist($task);
        $this->entityManager->flush();

        return $task;
    }
}
