<?php

namespace App\Repository;

use App\Entity\Order;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Order::class);
    }

    public function findByStatus($status, $id)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :val')
            ->andWhere('p.user = :id')
            ->setParameter('val', $status)
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByOffer($offer){
        return $this->createQueryBuilder('p')
            ->andWhere('p.offer = :val')
            ->setParameter('val', $offer)
            ->getQuery()
            ->getResult()
            ;
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
