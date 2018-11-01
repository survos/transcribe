<?php

namespace App\Controller;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/media")
 */
class MediaController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @Route("/", name="media_index", methods="GET")
     */
    public function index(MediaRepository $mediaRepository): Response
    {
        return $this->render('media/index.html.twig', ['media' => $mediaRepository->findAll()]);
    }


    /**
     * @Route("/new", name="media_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $medium = new Media();
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($medium);
            $em->flush();

            return $this->redirectToRoute('media_index');
        }

        return $this->render('media/new.html.twig', [
            'medium' => $medium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="media_show", methods="GET")
     */
    public function show(Media $medium): Response
    {
        return $this->render('media/show.html.twig', ['medium' => $medium]);
    }

    /**
     * @Route("/hide/{id}", name="media_hide", methods="GET")
     */
    public function hide(Media $media)
    {
        $media->setTranscriptRequested(!$media->getTranscriptRequested());
        $this->em->flush();
        return $this->redirectToRoute('project_show', $media->getProject()->rp());

    }

    /**
     * @Route("/passthru", name="media_passthru", methods="GET")
     */
    public function passthru(Request $request): Response
    {
        $fn = $request->get('fn');
        $fn = str_replace('file://localhost/', '', $fn);
        if (!file_exists($fn)) {
            throw new NotFoundHttpException("Can't find $fn");
        }
        return new BinaryFileResponse($fn);
        return $this->file($fn);
    }

    /**
     * @Route("/{id}/edit", name="media_edit", methods="GET|POST")
     */
    public function edit(Request $request, Media $medium): Response
    {
        $form = $this->createForm(MediaType::class, $medium);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('project_show', $medium->getProject()->rp());
        }

        return $this->render('media/edit.html.twig', [
            'medium' => $medium,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="media_delete", methods="DELETE")
     */
    public function delete(Request $request, Media $medium): Response
    {
        if ($this->isCsrfTokenValid('delete'.$medium->getId(), $request->request->get('_token'))) {
            $redirect = $this->redirectToRoute('project_show', $medium->getProject()->rp());

            $em = $this->getDoctrine()->getManager();
            $em->remove($medium);
            $em->flush();

            return $redirect;
        }

        return $this->redirectToRoute('media_index');
    }
}
