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

    public function researchByCible($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.mesures = :val')
            ->setParameter('val', $value)
            ->orderBy('i.date', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }
}
