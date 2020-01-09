<?php

namespace App\Repository;

use App\Entity\FinalCutPro;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method FinalCutPro|null find($id, $lockMode = null, $lockVersion = null)
 * @method FinalCutPro|null findOneBy(array $criteria, array $orderBy = null)
 * @method FinalCutPro[]    findAll()
 * @method FinalCutPro[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FinalCutProRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FinalCutPro::class);
    }

    // /**
    //  * @return FinalCutPro[] Returns an array of FinalCutPro objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?FinalCutPro
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
