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
    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        /** @var PositionRepository $repo */
        $repo = $this->getDoctrine()->getRepository(Position::class);

        $positionItems = $repo->findAll();

        return $this->render('home/index.html.twig', [
            'positionItems' => $positionItems
        ]);
    }

    /**
     * @Route("/basicform", name="basicForm")
     */
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
    public function addSuvalgo(Request $request)
    {
        $templateRepo = $this->getDoctrine()->getRepository(Template::class);
        $positionRepo = $this->getDoctrine()->getRepository(Position::class);

        $active = $request->get('active');
        $count = $request->get('count');

        $template = new Template();
        $template->setTitle($request->get('title'));

        $this->getDoctrine()->getManager()->persist($template);

        foreach ($active as $key => $value) {
            $position = $this->getDoctrine()->getRepository(Position::class)->find(1);
            $templatePosition = new PositionTemplate();
            $templatePosition->setTemplate($template)
                ->setPosition($position)
                ->setCount((int)$request->get('count'));
            $this->getDoctrine()->getManager()->persist($templatePosition);
        }
        $this->getDoctrine()->getManager()->flush();
        return new Response('success');
    }
}
