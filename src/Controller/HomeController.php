<?php

namespace App\Controller;

use App\Entity\Position;
use App\Entity\PositionTemplate;
use App\Entity\Template;
use App\Repository\PositionRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function index()
    {
        /** @var PositionRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Position::class);

        $positionItems = $repo->findAll();

        return $this->render('home/index.html.twig', [
            'positionItems' => $positionItems
        ]);
    }

    public function new(Request $request)
    {
        // creates a task and gives it some dummy data for this example
        $repo = $this->getDoctrine()->getRepository(Position::class);

        $positionItems = $repo->findAll();


        return $this->render('home/form.html.twig', [
            'positionItems' => $positionItems
        ]);
    }

    /**
     * @Route("/positiondata", name="positionData")
     */
    public function addTemplate(Request $request)
    {
        $templateRepo = $this->getDoctrine()->getRepository(Template::class);
        $active = $request->get('active');

        $templateId = $request->get('id');

        if($templateId == 0) {
            $template = new Template();
        }
        else
        {
            $template = $templateRepo->find($templateId);
        }
        $template->setTitle($request->get('title'));

        $this->getDoctrine()->getManager()->persist($template);
        $this->getDoctrine()->getManager()->flush();

        foreach ($active as $key => $value) {
            $exists = false;
            $position = $this->getDoctrine()->getRepository(Position::class)->find($key);
            $posTemplates = $template->getPositionTemplates();
            $templatePosition = new PositionTemplate();
            $templatePosition->setTemplate($template)
                ->setPosition($position)
                ->setCount((int)$request->get('count')[$key]);
            foreach($posTemplates as $key2 => $value2)
            {
                if($value2->getPosition() === $position)
                {
                    $exists = true;
                    break;
                }
            }

            if($exists)
            {}
            else {
                $template->addPositionTemplate($templatePosition);

                $this->getDoctrine()->getManager()->persist($templatePosition);
                $this->getDoctrine()->getManager()->persist($template);
            }
        }
        $this->getDoctrine()->getManager()->flush();
        return new Response('success');
    }

    /**
     * @Route("/positiondata2", name="positionData2")
     */
    public function editTemplate(Request $request)
    {
        $active = $request->get('active');

        $template->setTitle($request->get('title'));

        $this->getDoctrine()->getManager()->persist($template);
        $this->getDoctrine()->getManager()->flush();

        foreach ($active as $key => $value) {
            $position = $this->getDoctrine()->getRepository(Position::class)->find($key);
            $templatePosition = new PositionTemplate();
            $templatePosition->setTemplate($template)
                ->setPosition($position)
                ->setCount((int)$request->get('count')[$key]);
            $template->addPositionTemplate($templatePosition);

            $this->getDoctrine()->getManager()->persist($templatePosition);
            $this->getDoctrine()->getManager()->persist($template);
        }
        $this->getDoctrine()->getManager()->flush();
        return new Response('success');
    }
}
