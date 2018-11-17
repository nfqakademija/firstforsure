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
        $entityManager = $this->getDoctrine()->getManager();

        $templateRepo = $this->getDoctrine()->getRepository(Template::class);
        $active = $request->get('active');

        $templateId = $request->get('id');

        if ($templateId == 0) {
            $template = new Template();
        } else {
            $template = $templateRepo->find($templateId);
        }
        $template->setTitle($request->get('title'));

        $entityManager->persist($template);
        $entityManager->flush();

        $posTemplates = $template->getPositionTemplates();
        if($active !== null) {
            foreach ($active as $key => $value) {
                $exists = false;
                $position = $this->getDoctrine()->getRepository(Position::class)->find($key);
                $templatePosition = new PositionTemplate();
                $templatePosition->setEdited(true);
                $templatePosition->setTemplate($template)
                    ->setPosition($position)
                    ->setCount((int)$request->get('count')[$key]);
                $position->setCount((int)$request->get('count')[$key]);
                $entityManager->persist($templatePosition);
                foreach ($posTemplates as $key2 => $value2) {
                    if ($value2->getPosition() === $position) {
                        $value2->setPosition($position);
                        $value2->setEdited(true);
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $template->addPositionTemplate($templatePosition);

                    $entityManager->persist($templatePosition);
                    $entityManager->persist($template);
                }
            }
        }
        else
        {
            if($posTemplates !== null)
            {
                foreach($posTemplates as $templ)
                {
                    $template->removePositionTemplate($templ);
                }
            }
        }
        $posTemplates = $template->getPositionTemplates();
        foreach ($posTemplates as $pos => $val) {
            if ($val->getEdited() === null) {
                $template->removePositionTemplate($val);
            }
        }

        $entityManager->persist($template);
        $entityManager->flush();

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/resetdatabase", name="resetdb")
     */
    public function resetDatabase()
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(PositionTemplate::class);
        $entities = $repository->findAll();

        foreach ($entities as $entity) {
            $em->remove($entity);
        }
        $em->flush();

        $repository = $em->getRepository(Template::class);
        $entities = $repository->findAll();

        foreach ($entities as $entity) {
            $em->remove($entity);
        }
        $em->flush();


        $repository = $em->getRepository(Position::class);
        $entities = $repository->findAll();

        foreach ($entities as $entity) {
            $em->remove($entity);
        }
        $em->flush();

        return new Response('', Response::HTTP_OK);
    }
}
