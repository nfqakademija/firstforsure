<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.14
 * Time: 18.11
 */

namespace App\Controller\Admin;
use App\Entity\Position;
use App\Entity\Template;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;


class TemplateAdminController extends BaseAdminController
{
    public function newAction()
    {
        // creates a task and gives it some dummy data for this example
        $repo = $this->getDoctrine()->getRepository(Position::class);

        $positionItems = $repo->findAll();


        return $this->render('admin/template/edit.html.twig', [
            'id' => 0,
            'title' => '',
            'positionItems' => $positionItems
        ]);
    }

    public function editAction()
    {
        $templRepo = $this->getDoctrine()->getRepository(Template::class);
        $posRepo = $this->getDoctrine()->getRepository(Position::class);
        $id = $this->request->query->get('id');

        $activeItem = $templRepo->find($id);

        $activePositionItems = $activeItem->getPositionTemplates();
        $positionItems = $posRepo->findAll();

        foreach ($positionItems as $key => $value)
        {
            foreach ($activePositionItems as $key2 => $value2)
            {
                if($value2->getPosition()->getId() === $value->getId()){
                    $value->setCount($value2->getCount());
                }
            }
        }

        return $this->render('admin/template/edit.html.twig', [
            'id' => $activeItem->getId(),
            'title' => $activeItem->getTitle(),
            'positionItems' => $positionItems
        ]);
    }
}