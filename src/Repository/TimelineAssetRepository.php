<?php

namespace App\Repository;

use App\Entity\TimelineAsset;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TimelineAsset|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimelineAsset|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimelineAsset[]    findAll()
 * @method TimelineAsset[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimelineAssetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimelineAsset::class);
    }

//    /**
//     * @return TimelineAsset[] Returns an array of TimelineAsset objects
//     */
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
    public function findOneBySomeField($value): ?TimelineAsset
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
