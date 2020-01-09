<?php

namespace App\Repository;

use App\Entity\TimelineFormat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method TimelineFormat|null find($id, $lockMode = null, $lockVersion = null)
 * @method TimelineFormat|null findOneBy(array $criteria, array $orderBy = null)
 * @method TimelineFormat[]    findAll()
 * @method TimelineFormat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TimelineFormatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimelineFormat::class);
    }

//    /**
//     * @return TimelineFormat[] Returns an array of TimelineFormat objects
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
    public function findOneBySomeField($value): ?TimelineFormat
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
