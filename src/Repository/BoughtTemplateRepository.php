<?php

namespace App\Repository;

use App\Entity\BoughtTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BoughtTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method BoughtTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method BoughtTemplate[]    findAll()
 * @method BoughtTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BoughtTemplateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BoughtTemplate::class);
    }

    // /**
    //  * @return Position[] Returns an array of Position objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Position
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
