<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.30
 * Time: 13.19
 */

namespace App\Controller\Admin;

use App\Entity\Offer;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends BaseAdminController
{
    /**
     * @Route("/admin/dashboard", name="dashboard")
     */
    public function makeDashboard(Request $request)
    {
        $repo = $this->getDoctrine()->getRepository(Offer::class);

        $viewed = $repo->findByStatus("Peržiūrėtas");
        $viewedCount = count($viewed);

        $sent = $repo->findByStatus("Išsiųstas");
        $sentCount = count($sent);

        return $this->render('admin/dashboard.html.twig', [
            'viewed' => $viewed,
            'viewedCount' => $viewedCount,
            'sent' => $sent,
            'sentCount' => $sentCount
        ]);
    }
}