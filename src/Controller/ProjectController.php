<?php

namespace App\Controller;

use App\Entity\Marker;
use App\Entity\Project;
use App\Entity\Word;
use App\Form\MarkerFormType;
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

class ProjectController extends AbstractController
{

    private $em;
    private $projectRepository;
    private $markerRepository;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->projectRepository = $em->getRepository(Project::class);
        $this->markerRepository = $em->getRepository(Marker::class);
    }

    /**
     * @Route("/", name="project")
     */
    public function index()
    {
        return $this->render('project/index.html.twig', [
            'projects' => $this->projectRepository->findAll()
        ]);
    }

    /**
     * @Route("/project/{id}", name="project_show")
     */
    public function show(Request $request, Project $project)
    {
        $markers = $this->markerRepository->findByProject($project);

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'markers' => $markers,
            'markerSummary' => $this->markerRepository->findMarkerDrationByColor($project)
        ]);
    }

    /**
     * @Route("/{id}/reorder", name="marker_reorder", methods="GET|POST")
     */
    public function reorder(Request $request, Project $project): Response
    {
        $ids = $request->get('marker');
        foreach ($ids as $idx=>$id) {
            $marker = $this->em->getRepository(Marker::class)->find($id);
            $marker->setIdx($idx);
        }
        $this->em->flush();
        return $this->redirectToRoute('project_show', $project->rp());
    }


    /**
     * @Route("/select-markers/{id}", name="project_select_markers")
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
     * @Route("/{id}/subtitles.{_format}", name="project_subtitles")
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
     * @Route("/{code}/markers.{_format}", name="project_markers")
     * @ Entity("project", expr="repository.findOneBy({"code": code)")
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
                'note' => $marker->getNote()
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
