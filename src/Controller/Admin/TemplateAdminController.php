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
use App\Service\Admin\Template\TemplateManager as TemplateManager;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;


class TemplateAdminController extends BaseAdminController
{
    public function copyAction(){
        $em = $this->getDoctrine()->getManager();
        $templRepo = $this->getDoctrine()->getRepository(Template::class);
        $id = $this->request->query->get('id');
        $activeItem = $templRepo->find($id);

        $template = clone $activeItem;

        $posItems = $activeItem->getPositionTemplates();

        foreach ($posItems as $templ)
        {
            $clone = clone $templ;
            $em->persist($clone);
            $template->addPositionTemplate($clone);
        }
        $template->setTitle($template->getTitle()." (Kopija)");
        $em->persist($template);
        $em->flush();

        return $this->redirect("/admin/?entity=Template&action=list&menuIndex=3&submenuIndex=-1");
    }

    public function newAction()
    {
        // creates a task and gives it some dummy data for this example
        $repo = $this->getDoctrine()->getRepository(Position::class);

        $positionTimeItems = $repo->findByTime(true);
        $positionNoTimeItems = $repo->findByTime(false);


        return $this->render('admin/template/edit.html.twig', [
            'positionTimeItems' => $positionTimeItems,
            'positionNoTimeItems' => $positionNoTimeItems
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
        $positionTimeItems = $posRepo->findByTime(true);
        $positionNoTimeItems = $posRepo->findByTime(false);

        foreach ($positionItems as $value)
        {
            foreach ($activePositionItems as $value2)
            {
                if($value2->getPosition()->getId() === $value->getId()){
                    $value->setCount($value2->getCount());
                }
            }
        }

        return $this->render('admin/template/edit.html.twig', [
            'id' => $activeItem->getId(),
            'title' => $activeItem->getTitle(),
            'positionTimeItems' => $positionTimeItems,
            'positionNoTimeItems' => $positionNoTimeItems
        ]);
    }

    /**
     * @param Request $request
     * @param TemplateManager $templateManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function makeTemplate(Request $request, TemplateManager $templateManager)
    {
        $templateManager->makeTemplate($request);

        return $this->redirect("/admin/?entity=Template&action=list&menuIndex=3&submenuIndex=-1");
    }
}