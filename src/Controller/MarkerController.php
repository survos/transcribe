<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class MarkerController extends AbstractController
{
    /**
     * @Route("/marker", name="marker")
     */
    public function index()
    {
        return $this->render('marker/index.html.twig', [
            'controller_name' => 'MarkerController',
        ]);
    }

}
