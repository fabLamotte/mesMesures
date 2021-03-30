<?php

namespace App\Repository;

use App\Entity\ProfilSportif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProfilSportif|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProfilSportif|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProfilSportif[]    findAll()
 * @method ProfilSportif[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProfilSportifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProfilSportif::class);
    }

    // /**
    //  * @return ProfilSportif[] Returns an array of ProfilSportif objects
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
    public function findOneBySomeField($value): ?ProfilSportif
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
