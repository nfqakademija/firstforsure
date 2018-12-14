<?php

namespace App\Repository;

use App\Entity\OfferTemplate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method OfferTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method OfferTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method OfferTemplate[]    findAll()
 * @method OfferTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfferTemplateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OfferTemplate::class);
    }

    public function findCheckedOfferTemplate($status, $id)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :val')
            ->andWhere('p.offer = :id')
            ->setParameter('val', $status)
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }
}
