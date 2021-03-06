<?php

namespace App\Repository;

use App\Entity\Participant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Participant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Participant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Participant[]    findAll()
 * @method Participant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ParticipantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Participant::class);
    }

    public function findAllEmail()
    {
        return $this->createQueryBuilder('p')
            ->select('p.email')
            //->andWhere('p.isActive = 1')
            ->getQuery()
            ->getResult();
    }

    public function findUserActivityByEmail($email) {
        return $this->createQueryBuilder('p')
            ->select('p.isActive')
            ->andWhere('p.email = :value')
            ->setParameter('value', $email)
            ->getQuery()
            ->getResult();
    }

    public function findActiveUsers() {
        return $this->createQueryBuilder('p')
            ->andWhere('p.isDeleted = :value')
            ->setParameter('value', 0)
            ->getQuery()
            ->getResult();
    }

    // /**
    //  * @return Participant[] Returns an array of Participant objects
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
    public function findOneBySomeField($value): ?Participant
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
