<?php

namespace App\Repository;

use App\Entity\TripStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TripStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method TripStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method TripStatus[]    findAll()
 * @method TripStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TripStatus::class);
    }

    // /**
    //  * @return TripStatus[] Returns an array of TripStatus objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    public function findByName($value): ?TripStatus
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.name = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }

}
