<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.17
 * Time: 15.06
 */

namespace App\Controller\Admin;

use App\Entity\Template;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class OfferAdminController extends BaseAdminController
{
    public function newAction()
    {
        // creates a task and gives it some dummy data for this example
        $repo = $this->getDoctrine()->getRepository(Template::class);

        $templateItems = $repo->findAll();

        return $this->render('admin/offer/edit.html.twig', [
            'templateItems' => $templateItems
        ]);
    }
}