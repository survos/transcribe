<?php

namespace App\Repository;

use App\Entity\Marker;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Marker|null find($id, $lockMode = null, $lockVersion = null)
 * @method Marker|null findOneBy(array $criteria, array $orderBy = null)
 * @method Marker[]    findAll()
 * @method Marker[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MarkerRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Marker::class);
    }


    /**
     * @return Marker[] Returns an array of Marker objects
     */
    public function findByProject(Project $project)
    {
        return $this
            ->createQueryBuilder('marker')
            ->where('marker.media IN (:media)')
            ->setParameter('media', $project->getMedia())
            ->orderBy('marker.idx', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findMarkerDrationByColor(Project $project)
    {
        $colors = [];
        foreach ($this->findByProject($project) as $marker) {
            if (!isset($colors[$marker->getColor()])) {
                $colors[$marker->getColor()] = 0;
            }
            $colors[$marker->getColor()] += $marker->getDuration();
        }
        return $colors;
    }


    /*
    public function findOneBySomeField($value): ?Marker
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
