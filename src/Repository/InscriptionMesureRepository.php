<?php

namespace App\Repository;

use App\Entity\InscriptionMesure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InscriptionMesure|null find($id, $lockMode = null, $lockVersion = null)
 * @method InscriptionMesure|null findOneBy(array $criteria, array $orderBy = null)
 * @method InscriptionMesure[]    findAll()
 * @method InscriptionMesure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InscriptionMesureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InscriptionMesure::class);
    }

    /**
     * Fonction retournant la derniere mesure de l'utilisateur dans la mesure concernée
     */
    public function findLastDataByUserAndMesure($user, $mesure){
        return $this->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.mesures = :mesure')
            ->orderBy('m.date', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('mesure', $mesure)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Fonction retournant la derniere mesure de l'utilisateur dans la mesure concernée
     */
    public function findFirstDataByUserAndMesure($user, $mesure){
        return $this->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.mesures = :mesure')
            ->orderBy('m.date', 'DESC')
            ->setParameter('user', $user)
            ->setParameter('mesure', $mesure)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    
    /**
     * Fonction retournant la mesure concernée si elle rentre dans la portion de date concernée
     */
    public function findDataOneWeek($user, $mesure, $debut, $max){
        return $this->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.mesures = :mesure')
            ->andWhere('m.date BETWEEN :debut and :max')
            ->setParameter('user', $user)
            ->setParameter('mesure', $mesure)
            ->setParameter('debut', $debut)
            ->setParameter('max', $max)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
