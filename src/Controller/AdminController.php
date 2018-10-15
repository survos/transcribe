<?php

namespace App\Controller;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class AdminController extends BaseAdminController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function displayAction(Request $request)
    {
        return $this->redirectToRoute('media_show', ['id' => $request->get('id')]);
    }
}
