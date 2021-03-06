<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.30
 * Time: 13.19
 */

namespace App\Controller\Admin;

use App\Entity\Position;
use App\Service\Admin\Dashboard\CounterService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends BaseAdminController
{
    /**
     * @Route("/admin/dashboard", name="dashboard")
     */
    public function makeDashboard()
    {
        $positions = $this->getDoctrine()->getRepository(Position::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'offerCounts' => $this->get(CounterService::class)->getOfferCounts($this->getUser()->getId()),
            'orderCounts' => $this->get(CounterService::class)->getOrderCounts($this->getUser()->getId()),
            'positions' => $positions,
        ]);
    }
}