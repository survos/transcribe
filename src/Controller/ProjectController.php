<?php

namespace App\Controller;

use App\Entity\Marker;
use App\Entity\Project;
use App\Form\MarkerFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

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
        $markers = $this->markerRepository->createQueryBuilder('marker')
            ->where('marker.media IN (:media)')
            ->setParameter('media', $project->getMedia())
            ->getQuery()
            ->getResult();

        return $this->render('project/show.html.twig', [
            'project' => $project,
            'markers' => $markers
        ]);
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
            'switchForm' => $switchForm->createView(),
            'object' => getenv('OFFLINE') ? null : null // $object
        ]);
    }


}
