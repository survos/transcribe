<?php

namespace App\Repository;

use App\Entity\Clip;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Clip|null find($id, $lockMode = null, $lockVersion = null)
 * @method Clip|null findOneBy(array $criteria, array $orderBy = null)
 * @method Clip[]    findAll()
 * @method Clip[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClipRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Clip::class);
    }

//    /**
//     * @return Clip[] Returns an array of Clip objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Clip
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
