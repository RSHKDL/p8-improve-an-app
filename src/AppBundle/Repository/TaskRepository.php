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

    /**
     * @param User $user
     * @param bool $isDone
     * @return array
     */
    public function findAllByUser(User $user, $isDone = false): array
    {
        $qb = $this->createQueryBuilder('t')
            ->where('t.author = :user')
            ->andWhere('t.isDone = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $isDone);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param ?string $filter
     * @param bool $isDone
     * @return array
     */
    public function findAllWithFilter($filter = null, $isDone = false): array
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
     * @return array
     */
    public function findAllAnonymous()
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where('t.author IS NULL');

        return $qb->getQuery()->getResult();
    }
}
