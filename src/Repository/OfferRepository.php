<?php

namespace App\Repository;

use App\Entity\Offer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Offer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Offer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Offer[]    findAll()
 * @method Offer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfferRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Offer::class);
    }

    public function findByMd5($md5)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.md5 = :val')
            ->setParameter('val', $md5)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByStatus($status, $id)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :val')
            ->andWhere('p.user = :id')
            ->setParameter('val', $status)
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

    public function findCountOfStatuses($id)
    {
        return $this->createQueryBuilder('p')
            ->select('p.status, COUNT(p.status)')
            ->andWhere('p.user = :id')
            ->setParameter('id', $id)
            ->groupBy('p.status')
            ->getQuery()
            ->getScalarResult();
    }

    public function findCountByStatus($status, $id)
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->andWhere('p.status = :val')
            ->andWhere('p.user = :id')
            ->setParameter('val', $status)
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
