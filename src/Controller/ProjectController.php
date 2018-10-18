<?php

namespace App\Controller;

use App\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

class ProjectController extends AbstractController
{

    private $em;
    private $projectRepository;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->projectRepository = $em->getRepository(Project::class);
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
;

        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

}
