<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.30
 * Time: 13.19
 */

namespace App\Controller\Admin;

use App\Entity\Offer;
use App\Entity\Order;
use App\Entity\Position;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends BaseAdminController
{
    /**
     * @Route("/admin/dashboard", name="dashboard")
     */
    public function makeDashboard(Request $request, TranslatorInterface $translator)
    {
        $repo = $this->getDoctrine()->getRepository(Offer::class);
        $repoOrder = $this->getDoctrine()->getRepository(Order::class);
        $posRepo = $this->getDoctrine()->getRepository(Position::class);

        $user = $this->getUser();

        $viewed = $repo->findByStatus("Peržiūrėtas", $user->getId());
        $viewedCount = count($viewed);

        $sent = $repo->findByStatus("Išsiųstas", $user->getId());
        $sentCount = count($sent);

        $came = $repoOrder->findByStatus("Atsakytas", $user->getId());
        $orderCount = count($came);

        $accept = $repoOrder->findByStatus("Patvirtintas", $user->getId());
        $acceptCount = count($accept);

        $positions = $posRepo->findAll();



        return $this->render('admin/dashboard.html.twig', [
            'viewed' => $viewed,
            'viewedCount' => $viewedCount,
            'sent' => $sent,
            'sentCount' => $sentCount,
            'positions' => $positions,
            'orderCount' => $orderCount,
            'acceptCount' => $acceptCount,
        ]);
    }
}