<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Task;
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
     * @return array
     */
    public function findAllAnonymous()
    {
        $qb = $this->createQueryBuilder('t');
        $qb->where('t.author IS NULL');

        return $qb->getQuery()->getResult();
    }
}
