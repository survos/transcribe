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
use App\Entity\TimelineFormat;
use Doctrine\ORM\EntityManagerInterface;

class TimelineHelper
{
    private $em;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
        $this->markerRepo = $entityManager->getRepository(Marker::class);
        $this->mediaRepo = $entityManager->getRepository(Media::class);
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

            foreach ($marker->getBRolls() as $broll) {
                $brollMedia = $broll->getMedia();
                $mediaList[$brollMedia->getCode()] = $brollMedia;
                // dump($broll); die();
            }
            $media = $marker->getMedia();
            $mediaList[$media->getCode()] = $media;

            /** @var Media $brollMedia */
            /* old way, with random photos
            if ($idx < $photos->count()) {

                $brollMedia = $photos->getValues()[$idx];
                $broll = (new BRoll())
                    ->setMedia($brollMedia);
                $mediaList[$brollMedia->getCode()] = $brollMedia;
                $marker->addBRoll($broll);
            }
            */
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
                // this is the offset WITHIN the clkip!
                // $offset += round($BRoll->calculateStartWordTime()); // round(($marker->getDuration())); // ??
                // $clip->setTrackOffset($offset);

            }
            // if there's a photo, add a short delay, (otherwise a transition?)
            $offset += round(($marker->getDuration()));
            if ($marker->getBRolls()->count()) {
                $offset +=  2; // add a short delay
            }
        }


        return $timeline;


    }

    public function updateTimelineFromXml(\SimpleXMLElement $xml, Timeline $timeline): Timeline
    {

        foreach ($xml->resources->children() as $resource) {
            switch ($resource->getName()) {
                case 'format':
                    $format = (new TimelineFormat());
                    $timeline
                        ->addTimelineFormat($format);
                    $format
                        ->setFromXml($resource, $timeline);
                    break;
                case 'asset':
                    $asset = (new TimelineAsset())
                        ->setFromXml($resource, $timeline);

                    // attach the media from the id/code? Or the marker?  Ughf
                    // $media = $this->m
                    $timeline->addTimelineAsset($asset);
                    break;
                default:
                    throw new \Exception($resource->getName() . ' not handled in setFromXml()');
            }
        }
        // dump($this->getTimelineAssets()); die();



        $spline = $xml->library->event->project->sequence->spine;

        $timeline
            ->setTotalDuration(Timeline::fractionalSecondsToTime($xml->library->event->project->sequence['duration']));

        foreach ($spline->children() as $splineItem) {
            $clip = new Clip();
            $timeline->addClip($clip);

            $clip->setFromXml($splineItem, $timeline);
            switch ($type = $splineItem->getName()) {
                case 'clip':
                    // dump($splineItem);
                    // break;
                case 'asset-clip':
                    foreach ($splineItem->video as $photoItem) {
                        $photo = new Clip();
                        $timeline->addClip($photo);
                        $photo->setFromXml($photoItem, $timeline);
                    }
                    break;
                case 'gap':
                    break;
                default:
                    throw new \Exception("Unhandled type: $type");
            }
        }

        return $timeline;
    }



}