<?php

namespace App\Repository;

use App\Entity\Trp;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Trp|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trp|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trp[]    findAll()
 * @method Trp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrpRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trp::class);
    }

    // /**
    //  * @return Trp[] Returns an array of Trp objects
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
    public function findOneBySomeField($value): ?Trp
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
