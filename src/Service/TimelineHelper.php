<?php
/**
 * Created by PhpStorm.
 * User: tac
 * Date: 10/29/18
 * Time: 8:56 PM
 */

namespace App\Service;


use App\Entity\BRoll;
use App\Entity\Clip;
use App\Entity\Marker;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Timeline;
use App\Entity\TimelineAsset;
use Doctrine\ORM\EntityManagerInterface;

class TimelineHelper
{
    private $em;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->markerRepo = $entityManager->getRepository(Marker::class);
    }

    public function updateTimelineFromProject(Project $project, Timeline $timeline=null): Timeline
    {
        if (empty($timeline)) {
            $timeline = new Timeline();
        }
        $timeline
            ->setProject($project);

        // assets are media used, we want only the media in the markers we're using
        // for testing, one photo per media
        $photos = $project->getMedia()->filter(function (Media $media) {
            return $media->getType() === 'photo' && ($media->getHeight() < $media->getWidth() && ($media->getTranscriptRequested()));
        });

        $markers = $this->markerRepo->findByProject($project, $maxDuration = $timeline->getMaxDuration());

        $mediaList = [];
        foreach ($markers as $idx=>$marker) {
            $media = $marker->getMedia();
            $mediaList[$media->getCode()] = $media;
            if ($idx < $photos->count()) {

                /** @var Media $brollMedia */
                $brollMedia = $photos->getValues()[$idx];
                $broll = (new BRoll())
                    ->setMedia($brollMedia);
                $mediaList[$brollMedia->getCode()] = $brollMedia;
                $marker->addBRoll($broll);
            }
        }

        $timeline->setMaxDuration($timeline->calcDuration());

            $assets = [];
            // only import the media we're using
            /** @var Media $media */
            foreach ($mediaList as $mediaCode => $media) {

                $format = $media->createTimelineFormat();
                if (empty($formats[$format->getCode()])) {
                    $formats[$format->getCode()] = $format;
                    $timeline->addTimelineFormat($format);
                } else {
                    $format = $formats[$format->getCode()]; // don't duplicate it.
                }

                $asset = (new TimelineAsset())
                    ->setMedia($media)
                    ->setHasAudio(!$media->isPhoto())
                    // ->setSrc($media->getPath())
                    // ->setDuration($media->getDuration() / 10)
                    ->setSrc($media->getProject()->getBasePath() . '/' . $media->getFilename())
                    ->setName($media->getBaseName())
                    ->setDuration($media->getDuration())
                    ->setFormat($format)
                    ->setCode($media->getCode())
                ;
                $timeline->addTimelineAsset($asset);
                $assets[$asset->getCode()] = $asset;
            }

        $offset = 36033; // hack for 1 hour
        foreach ($markers as $idx=>$marker) {
            // now go through the markers and add the clips
            $clip = (new Clip())
                ->setName($marker->getTitle())
                ->setStart($marker->getStartTime())
                ->setDuration($marker->getDuration())
                ->setTrackOffset($offset)
                ->setAsset($assets[$marker->getMedia()->getCode()]);
            $timeline
                ->addClip($clip);

            foreach ($marker->getBRolls() as $BRoll) {
                $clip->addBRoll($BRoll);
            }
            $offset += round(($marker->getDuration())); // ??
        }


        return $timeline;


    }


}