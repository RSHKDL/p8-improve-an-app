<?php


namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class UserRepository
 * @author ereshkidal
 */
class UserRepository extends ServiceEntityRepository
{
    /**
     * TaskRepository constructor.
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @return array
     */
    public function findAllNonAdmin()
    {
        $qb = $this->createQueryBuilder('u');
        $qb
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_USER%')
        ;

        return $qb->getQuery()->getResult();
    }
}
