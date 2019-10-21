<?php

namespace App\Repository;

use App\Entity\TripPlace;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TripPlace|null find($id, $lockMode = null, $lockVersion = null)
 * @method TripPlace|null findOneBy(array $criteria, array $orderBy = null)
 * @method TripPlace[]    findAll()
 * @method TripPlace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripPlaceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TripPlace::class);
    }

    // /**
    //  * @return TripPlace[] Returns an array of TripPlace objects
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

    /*
    public function findOneBySomeField($value): ?TripPlace
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
