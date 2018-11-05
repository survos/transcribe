<?php

namespace App\Controller;

use App\Entity\BRoll;
use App\Entity\Marker;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\Timeline;
use App\Entity\Word;
use App\Form\MarkerFormType;
use App\Form\TimelineType;
use App\Service\TimelineHelper;
use Done\Subtitles\Subtitles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Time;

class ProjectController extends AbstractController
{

    private $em;
    private $projectRepository;
    private $mediaRepository;
    private $markerRepository;
    private $timelineHelperService;

    public function __construct(EntityManagerInterface $em, TimelineHelper $helper)
    {
        $this->em = $em;
        $this->projectRepository = $em->getRepository(Project::class);
        $this->markerRepository = $em->getRepository(Marker::class);
        $this->mediaRepository = $em->getRepository(Media::class);
        $this->brollRepository = $em->getRepository(BRoll::class);
        $this->timelineHelperService = $helper;
    }

    /**
     * @Route("/", name="project")
     */
    public function index()
    {
        $projects = $this->projectRepository->findAll();
        foreach ($projects as $project) {
            $timeline = $this->timelineHelperService->updateTimelineFromProject($project);
            $project->addTimeline($timeline);
        }
        return $this->render('project/index.html.twig', [
            'projects' => $projects
        ]);
    }

    /**
     * @Route("/project/{code}", name="project_show")
     */
    public function show(Request $request, Project $project)
    {
        $markers = $this->markerRepository->findByProject($project);

        $timeline = (new Timeline())
            ->setCode($project->getCode() . '_rough_cut')
            ->setMaxDuration(180)
            ->setGapTime(1)
            ->setProject($project);
        $form = $this->createForm(TimelineType::class, $timeline);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->redirectToRoute('project_xml', $project->rp(['max'=> $timeline->getMaxDuration()]));
        }

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'markers' => $markers,
            'timelineForm' => $form->createView(),
            'markerSummary' => $this->markerRepository->findMarkerDrationByColor($project)
        ]);
    }

    /**
     * @Route("/project/clear-broll/{code}", name="project_clear_broll")
     */
    public function clearBroll(Request $request, Project $project)
    {
        if ($brollId = $request->get('broll_id')) {
            $broll = $this->brollRepository->find($brollId);
            $this->em->remove($broll);

        } else {
            foreach ($project->getMedia() as $photo)
            {
                foreach ($photo->getBRolls() as $BRoll) {
                    $photo->removeBRoll($BRoll);
                }
            }
        }
        $this->em->flush();
        return $this->redirectToRoute('project_add_photos_to_markers', $project->rp());
    }

    /**
     * @Route("/project/photos/{code}", name="project_add_photos_to_markers")
     */
    public function addPhotosToClips(Request $request, Project $project)
    {

        // better would be to use a Symfony form, but this is faster to code.
        if ($markerId = $request->get('marker_id')) {
            /** @var Marker $marker */
            $marker = $this->markerRepository->find($markerId);
            $startWord = $request->get('start_word');
            $photo = $this->mediaRepository->find($request->get('photo_id'));
            $broll = (new BRoll())
                ->setStartWord($startWord)
                ->setCode($startWord . '_' . $photo->getCode() . '_' . $marker->getTitle())
                ->setMedia($photo);
            $marker->addBRoll($broll);
            $this->em->persist($broll);
            $this->em->flush();
            return $this->redirectToRoute('project_add_photos_to_markers', $project->rp());
        }

        $markers = $this->markerRepository->findByProject($project);

        // go through all the markers and find the next one that needs to be done
        $needMarker = true;
        $nextMarkerId = null;
        foreach ($markers as $marker) {
            if ($marker->getBRolls()->count()) {
                $needMarker = true;
            } else {
                if ($needMarker ) {
                    $nextMarkerId = $marker->getId();
                    $needMarker = false; // we have one
                }
            }
        }

        $photos = $this->mediaRepository->findBy([
            'project' => $project,
            'type' => 'photo',
            'transcriptRequested' => true
        ]);


        return $this->render('project/addPhotosToClips.html.twig', [
            'project' => $project,
            'markers' => $markers,
            'nextMarkerId' => $nextMarkerId,
            'photos' => $photos

        ]);
    }

    /**
     * @Route("/{code}/reorder", name="marker_reorder", methods="GET|POST")
     */
    public function reorder(Request $request, Project $project): Response
    {
        $ids = $request->get('marker');
        foreach ($ids as $idx=>$id) {
            $marker = $this->em->getRepository(Marker::class)->find($id);
            $marker->setIdx($idx+1);
        }
        $this->em->flush();
        return $this->redirectToRoute('project_show', $project->rp());
    }


    /**
     * @Route("/select-markers/{code}", name="project_select_markers")
     */
    public function selectMarkers(Request $request, Project $media)
    {
        /*
        $switchForm = $this->createForm(SwitchMediaFormType::class, $media);
        $switchForm->handleRequest($request);
        if ($switchForm->isSubmitted() && $switchForm->isValid()) {
            // we want the new media property of the form
            $newMedia = $switchForm->get('media')->getData();
            return $this->redirectToRoute('media_show', $newMedia->rp());
            // jump to new media
        }
        */


        $marker = (new Marker())
            // ->setMedia($media)
        ;

        $form = $this->createForm(MarkerFormType::class, $marker);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($marker);
            $this->em->flush();
            // return JSON if it's an ajax request?
            return $this->redirectToRoute('media_show', $media->rp());
            // return new JsonResponse(['status' => 'ok']);
        }

        // $object = $this->getStorageObject($media);
        return $this->render('media/show.html.twig', [
            'media' => $media,
            'form' => $form->createView(),
            // 'switchForm' => $switchForm->createView(),
            'object' => getenv('OFFLINE') ? null : null // $object
        ]);
    }

    /**
     * @param Request $request
     * @Route("/{code}/subtitles.{_format}", name="project_subtitles")
     */
    public function subtitles(Request $request, Project $project, $_format='srt')
    {
        $subtitles = new Subtitles();
        foreach ($this->markerRepository->findByProject($project) as $marker)
        {
            $subtitles->add($marker->getStartTime() / 10, $marker->getEndTime() / 10, $marker->getNote());
        }

        switch ($_format) {
            // case 'html': return new Response($subtitles->content());
            case 'json': return new JsonResponse($subtitles->getInternalFormat());
            case 'srt':
            case 'vtt':
                return new Response($subtitles->content($_format), 200, ['Content-Type' => 'text/plain']);

        }
        // $subtitles->save('subtitles.vtt');

    }

    /**
     * @param Request $request
     * @Route("/{code}/markers.{_format}", name="project_edl")
     */
    public function edl(Request $request, Project $project, $_format='html')
    {
        // really this should come from the timeline, but we're wasting all sorts of time!
        $markers = $this->markerRepository->findByProject($project);

        $x = [];
        $txt = "TITLE: Timeline 1
FCM: NON-DROP FRAME
";


        $frameRate = 29.97; // check!
        $start = 60 * 60;
        foreach ($markers as $idx=>$marker) {

            $end = $start + $marker->getDuration();

            $txt .= sprintf("\n%03d  AX       V     C        %s %s %s %s\n * FROM_CLIP NAME: %s", $idx+1,
                $this->framesToTC($marker->getStartTime() / 10 * $frameRate, $frameRate),
                $this->framesToTC($marker->getEndTime() / 10 * $frameRate, $frameRate),
                $this->framesToTC($start * $frameRate, $frameRate),
                $this->framesToTC($end * $frameRate, $frameRate)     ,
            $marker->getMedia()->getFilename()
                );
            $start = $end; // fraction?
        }

        file_put_contents($project->getCode() . '.edl', $txt);

        return new Response($txt, 200, ['Content-Type' => 'text/plain']);
    }

    private function createXml(Project $project, Timeline $timeline)
    {

        // really this should come from the timeline, but we're wasting all sorts of time!
        $markers = $this->markerRepository->findByProject($project, $maxDuration = $timeline->getMaxDuration());

        // only import the media we're using
        $mediaList = [];
        foreach ($markers as $marker) {
            $media = $marker->getMedia();
            $mediaList[$media->getCode()] = $media;
        }

        $photos = $project->getMedia()->filter(function (Media $media) {
            return $media->getType() === 'photo';
        });

        $xml = $this->renderView("fcpxml.twig", [
            'markers' => $markers,
            'photos' => $photos,
            // 'photo' => $photos[0],
            'timeline' => $timeline,
            'project' => $project,
            'mediaList' => $mediaList
        ]);

        // file_put_contents('../' . $project->getCode() . '-import.fcpxml', $xml);

        return $xml;

    }

    /**
     * @param Request $request
     * @Route("/{code}/fcpxml.{_format}", name="project_xml")
     */
    public function fcpxml(Request $request, Project $project, $_format='html')
    {

        // $xml = $this->createXml($project, (new Timeline())->setMaxDuration($request->get('max', 180)));
        $timeline = (new Timeline())
            ->setMaxDuration($request->get('max', 60));

        $timeline = $this->timelineHelperService->updateTimelineFromProject($project, $timeline);
        $xml = $this->renderView('timeline_xml.twig', [
            'timeline' => $timeline
        ]);

        // file_put_contents('/tmp/' . $project->getCode() . '-import.fcpxml', $xml);
        // format the raw xml
        if (function_exists('tidy_repair_string')) {
            $xml = tidy_repair_string($xml, ['input-xml'=> 1, 'indent' => 1, 'wrap' => 0, 'hide-comments' => false]);
        }



        return new Response($xml, 200, ['Content-Type' => 'text/xml']);
    }


    private function framesToTC($frames, $framerate) {
        $hours = floor( $frames / ( $framerate * 60 * 60 ) );
        $framesleft = $frames - ($hours * $framerate * 60 * 60);
        $minutes = floor( $framesleft / ( $framerate * 60 ) );
        $framesleft -= ( $minutes * $framerate * 60 );
        $seconds = floor( $framesleft / ( $framerate ) );
        $framesleft -= ( $seconds * $framerate );
        $tc = sprintf("%02d:%02d:%02d:%02d", $hours, $minutes, $seconds, $framesleft );
        return $tc;
    }

    /**
     * @param Request $request
     * @Route("/{code}/markers.{_format}", name="project_markers")
     */
    public function markers(Request $request, Project $project, $_format='html')
    {
        $markers = $this->markerRepository->findByProject($project);
        /* drat, this should work!!
        $encoders = array( new JsonEncode());
        $normalizers = array(new ObjectNormalizer());


        $serializer = new Serializer($normalizers, $encoders);
        $data = $serializer->normalize($markers, null, array('groups' => array('project')));
        dump($data); die();

        $json = $serializer->serialize($markers, 'json', ['groups'=>['project']]);
        dump($json); die();
        */

        $x = [];
        foreach ($markers as $marker) {
            $x[] = (object)[
                'startTime' => $marker->getStartTime(),
                'endTime' => $marker->getEndTime(),
                'color' => $marker->getColor(),
                'title' => $marker->getTitle(),
                'note' => $marker->getNote(),
                'idx' => (int)$marker->getIdx(),
                'media' => $marker->getMedia()->getFilename() // better be unique for now!
            ];
        }
        return new JsonResponse($x);

        switch ($_format) {
            // case 'html': return new Response($subtitles->content());
            case 'json': return new JsonResponse($subtitles->getInternalFormat());
            case 'srt':
            case 'vtt':
                return new Response($subtitles->content($_format), 200, ['Content-Type' => 'text/plain']);

        }
        // $subtitles->save('subtitles.vtt');

    }

}
