<?php
/**
 * Created by PhpStorm.
 * User: tac
 * Date: 10/29/18
 * Time: 8:56 PM
 */

namespace App\Service;


use App\Entity\Marker;
use App\Entity\Project;
use App\Entity\Timeline;
use Doctrine\ORM\EntityManagerInterface;

class TimelineHelper
{
    private $em;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->markerRepo = $entityManager->getRepository(Marker::class);
    }

    public function updateTimelineFromProject(Project $project, Timeline $timeline=null): self
    {
        if (empty($timeline)) {
            $timeline = new Timeline();
        }

        // really this should come from the timeline, but we're wasting all sorts of time!
        $markers = $this->markerRepo->findByProject($project, $maxDuration = $timeline->getMaxDuration());

        // only import the media we're using
        $mediaList = [];
        foreach ($markers as $marker) {
            $media = $marker->getMedia();
            $mediaList[$media->getCode()] = $media;
        }

        $xml = $this->renderView("fcpxml.twig", [
            'markers' => $markers,
            'photos' => $project->getMedia()->filter(function (Media $media) {
                return $media->getType() === 'photo';
            }),
            'timeline' => $timeline,
            'mediaList' => $mediaList
        ]);

        return $timeline;


    }


}