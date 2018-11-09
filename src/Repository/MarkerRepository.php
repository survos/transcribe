<?php

namespace App\Repository;

use App\Entity\Marker;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PhpParser\Node\Expr\Array_;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
    public function findByProject(Project $project, array $options = [])
    {
        $options = (new OptionsResolver())
            ->setDefaults([
                'maxDuration' => null, //  180,
                'maxMarkers' => 0,
                'lastMarker' => null,
                'timeline' => null
            ])->resolve($options);

        $markerQb =  $this
            ->createQueryBuilder('marker')
            ->where('marker.media IN (:media)')
            ->andWhere('marker.irrelevant <> true')
            ->andWhere('marker.hidden <> true')
            ->setParameter('media', $project->getMedia());

        if ($lastMarker = $options['lastMarker'])
        {
            $markerQb->andWhere('marker.idx <= :idx')
                ->setParameter('idx', $lastMarker->getIdx());
        }

        if ($maxMarkers = $options['maxMarkers']) {
            $markerQb
                ->setMaxResults($maxMarkers)
            ;
        }

        $markers = $markerQb
            ->orderBy('marker.idx', 'ASC')
            ->getQuery()
            ->getResult();

        // there's likely a better way!
        $duration = 0;

        if ($maxDuration = $options['maxDuration']) {
            $filteredMarkers = [];
            /** @var Marker $marker */
            foreach ($markers as $marker) {
                if ($duration <= $maxDuration) {
                    $filteredMarkers[] = $marker;
                    $duration = $duration + ($marker->getDuration()/10);
                }
                if ($duration > $maxDuration) {
                    break;
                }
            }
            return $filteredMarkers;
        } else {
            return $markers;
        }

        // return $markers;


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
