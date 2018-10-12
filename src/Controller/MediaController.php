<?php

namespace App\Controller;

use App\Entity\Media;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MediaController extends AbstractController
{

    private $em;
    private $mediaRepo;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->mediaRepo = $em->getRepository(Media::class);
    }

    /**
     * @Route("/media", name="media")
     */
    public function index()
    {
        return $this->render('media/index.html.twig', [
            'media' => $this->mediaRepo->findAll(),
            'controller_name' => 'MediaController',
        ]);
    }

    /**
     * @Route("/show/{id}", name="media_show")
     */
    public function show(Request $request, Media $media)
    {
        return $this->render('media/show.html.twig', [
            'media' => $media
        ]);
    }

}
