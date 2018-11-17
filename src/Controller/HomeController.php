<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\OfferTemplate;
use App\Entity\Position;
use App\Entity\PositionTemplate;
use App\Entity\Template;
use App\Repository\PositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints\Collection;

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
     * @Route("/maketemplate", name="maketemplate")
     */
    public function makeTemplate(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $templateRepo = $this->getDoctrine()->getRepository(Template::class);
        $active = $request->get('active');

        foreach ($active as $key => $value) {
            if ($value === "0") {
                unset($active[$key]);
            }
        }

        $templateId = $request->get('id');

        if ($templateId == 0) {
            $template = new Template();
        } else {
            $template = $templateRepo->find($templateId);
        }
        $template->setTitle($request->get('title'));

        $em->persist($template);
        $em->flush();

        $posTemplates = $template->getPositionTemplates();

        if ($active != null) {
            foreach ($posTemplates as $key => $value) {
                $index = $value->getPosition()->getId();
                if (!in_array($index, $active)) {
                    $price = $value->getCount() * $value->getPosition()->getPrice();
                    $template->minusPrice($price);
                    $em->remove($value);
                    $em->flush();
                }
            }
            foreach ($active as $key => $value) {
                $exists = false;
                $position = $this->getDoctrine()->getRepository(Position::class)->find($key);
                $templatePosition = new PositionTemplate();
                $templatePosition->setTemplate($template)
                    ->setPosition($position)
                    ->setCount((int)$request->get('count')[$key]);
                $position->setCount((int)$request->get('count')[$key]);

                foreach ($posTemplates as $key2 => $value2) {
                    if ($value2->getPosition() === $position) {
                        $oldPrice = $value2->getCount() * $value2->getPosition()->getPrice();
                        $value2->setPosition($position);
                        $value2->setCount((int)$request->get('count')[$key]);
                        $newPrice = $value2->getCount() * $value2->getPosition()->getPrice();
                        $template->minusPrice($oldPrice);
                        $template->addPrice($newPrice);
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $template->addPositionTemplate($templatePosition);

                    $template->addPrice((float)$request->get('sum')[$key]);
                    $em->persist($templatePosition);
                    $em->persist($template);
                }
            }
        } else {
            if ($posTemplates !== null) {
                foreach ($posTemplates as $templ) {
                    $template->removePositionTemplate($templ);
                }
                $template->setPrice(0);
            }
        }

        $em->flush();

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/makeoffer", name="makeoffer")
     */
    public function makeOffer(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $offerRepo = $this->getDoctrine()->getRepository(Offer::class);

        $active = $request->get('active');

        foreach ($active as $key => $value) {
            if ($value === "0") {
                unset($active[$key]);
            }
        }

        if ($active !== null) {
            $offerId = $request->get('id');

            if ($offerId == 0) {
                $offer = new Offer();
            } else {
                $offer = $offerRepo->find($offerId);
            }

            $offer->setClientEmail($request->get('clientEmail'));
            $offer->setClientName($request->get('clientName'));

            $time = new \DateTime();

            $hash = md5($request->get('clientEmail').$time->format('Y-m-d'));

            $offer->setMd5($hash);
            $em->flush();
            foreach ($active as $key => $value) {
                $template = $this->getDoctrine()->getRepository(Template::class)->find($key);
                $templateOffer = new OfferTemplate();
                $templateOffer->setOffer($offer)
                    ->setTemplate($template);
                $offer->addOfferTemplate($templateOffer);

                $em->persist($templateOffer);
                $em->persist($offer);
            }
            $em->flush();
            return $this->redirectToRoute('readoffer', ['md5' => $offer->getMd5()]);
        }
    }


    /**
     * @Route("/sendmail", name="sendmail")
     */
    public function mail(\Swift_Mailer $mailer)
    {
        $message = (new \Swift_Message('Hello Email'))
            ->setFrom('zrvtzrvt@gmail.com')
            ->setTo('gudauskas.osvaldas@gmail.com')
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'emails/registration.html.twig',
                    array('name' => $name)
                ),
                'text/html'
            )/*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        //$mailer->send($message);
        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/readoffer/{md5}", name="readoffer")
     */
    public function reademail($md5){
        $repo = $this->getDoctrine()->getRepository(Offer::class);

        $offer = $repo->findByMd5($md5);
        dump($offer);exit;
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


//        $repository = $em->getRepository(Position::class);
//        $entities = $repository->findAll();
//
//        foreach ($entities as $entity) {
//            $em->remove($entity);
//        }
//        $em->flush();

        return new Response('', Response::HTTP_OK);
    }
}
