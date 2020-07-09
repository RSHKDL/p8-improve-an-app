<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Task;
use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class TaskRepository
 * @author ereshkidal
 */
class TaskRepository extends ServiceEntityRepository
{
    /**
     * TaskRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function findAllByUser(User $user, bool $isDone = false): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.author = :user')
            ->andWhere('t.isDone = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $isDone);

        return $qb->getQuery()->getResult();
    }

    public function findAllWithFilter(?string $filter = null, bool $isDone = false): array
    {
        $qb = $this->createQueryBuilder('t')
            ->andWhere('t.isDone = :status')
            ->setParameter('status', $isDone);

        if ($filter !== null) {
            $qb
                ->andWhere('t.author = :term OR t.title = :term OR t.content = :term')
                ->setParameter('term', $filter);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Used only in PurgeTasksCommand.
     * @codeCoverageIgnore
     */
    public function findAllAnonymous(): array
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where('t.author IS NULL');

        return $qb->getQuery()->getResult();
    }
}
