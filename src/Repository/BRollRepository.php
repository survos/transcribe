<?php

namespace App\Repository;

use App\Entity\BRoll;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method BRoll|null find($id, $lockMode = null, $lockVersion = null)
 * @method BRoll|null findOneBy(array $criteria, array $orderBy = null)
 * @method BRoll[]    findAll()
 * @method BRoll[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BRollRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BRoll::class);
    }

//    /**
//     * @return BRoll[] Returns an array of BRoll objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?BRoll
    {
        return $this->createQueryBuilder('b')
            ->andWhere('b.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
