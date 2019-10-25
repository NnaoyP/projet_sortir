<?php

namespace App\Repository;

use App\Entity\Trip;
use App\Entity\TripStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * @method Trip|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trip|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trip[]    findAll()
 * @method Trip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TripRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trip::class);
    }

    // /**
    //  * @return Trip[] Returns an array of Trip objects
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


    public function findByFilter(ParameterBag $args): ?array
    {
        $queryBuilder = $this->createQueryBuilder('t');

        // recherche par site
        if(!empty($args->get('place'))) {
            $queryBuilder->innerJoin('t.place', 'p', join::WITH, 'p.id = :tripPlace')
                ->setParameter('tripPlace', $args->get('place'));
        }

        // recherche par nom de sortie
        if(!empty($args->get('name'))) {
            $queryBuilder->andWhere('t.name LIKE :tripName')
                ->setParameter('tripName', ('%'.$args->get('name').'%'));
        }

        // recherche par date de sorties (après le)
        if (!empty($args->get('startDate'))) {
            $queryBuilder->andWhere('t.startDate >= :tripStartDate')
                ->setParameter('tripStartDate', gmdate('Y-m-d h:i:s', strtotime($args->get('startDate'))));
        }

        // recherche par date de sorties (avant le)
        if (!empty($args->get('endDate'))) {
            $queryBuilder->andWhere('t.startDate <= :tripEndDate')
                ->setParameter('tripEndDate', gmdate('Y-m-d h:i:s', strtotime($args->get('endDate'))));
        }

        // recherche dont la sortie est organisée par l'utilisteur
        if (!empty($args->get('isOrganizer'))) {
            $queryBuilder->innerJoin('t.organizer', 'o', join::WITH, 'o.email = :userEmail')
                ->setParameter('userEmail', $args->get('email'));
        }

        // recherche dont l'utilisateur est un participant
        if (!empty($args->get('isParticipantOn'))) {
            $queryBuilder->innerJoin('t.participants', 'o', join::WITH, 'o.email = :userEmail')
                ->setParameter('userEmail', $args->get('email'));
        }

        // rechercher dont l'utilisateur ne participe pas
        if (!empty($args->get('isParticipantOff'))) {
            $queryBuilder->where(':userId NOT MEMBER OF t.participants')
                ->setParameter('userId', $args->get('userId'));
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
