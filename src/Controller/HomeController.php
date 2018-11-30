<?php

namespace App\Controller;

use App\Entity\BoughtTemplate;
use App\Entity\Message;
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
        $template->setStatus('Parduodama');

        $em->persist($template);
        $em->flush();

        $posTemplates = $template->getPositionTemplates();

        if ($active != null) {
            foreach ($posTemplates as $key => $value) {
                $index = $value->getPosition()->getId();
                if (!in_array($index, $active)) {
                    $price = $value->getCount() * $value->getPosition()->getPrice();
                    $reach = $value->getCount() * $value->getPosition()->getReach();
                    $template->minusPrice($price);
                    $template->minusReach($reach);
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
                        $oldReach = $value2->getCount() * $value2->getPosition()->getReach();
                        $value2->setPosition($position);
                        $value2->setCount((int)$request->get('count')[$key]);
                        $newPrice = $value2->getCount() * $value2->getPosition()->getPrice();
                        $newReach = $value2->getCount() * $value2->getPosition()->getReach();
                        $template->minusPrice($oldPrice);
                        $template->addPrice($newPrice);
                        $template->minusReach($oldReach);
                        $template->addReach($newReach);
                        $exists = true;
                        break;
                    }
                }
                if (!$exists) {
                    $template->addPositionTemplate($templatePosition);

                    $template->addPrice((float)$request->get('sum')[$key]);
                    $template->addReach((float)$request->get('sum2')[$key]);


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
                $template->setReach(0);
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
        $templRepo = $this->getDoctrine()->getRepository(Template::class);

        $active = $request->get('active');

        foreach ($active as $key => $value) {
            if ($value === "0") {
                unset($active[$key]);
            }
        }

        $offerId = $request->get('id');

        if ($offerId == 0) {
            $offer = new Offer();
        } else {
            $offer = $offerRepo->find($offerId);
        }

        $offer->setClientEmail($request->get('clientEmail'));
        $offer->setClientName($request->get('clientName'));
        $offer->setMessage($request->get('message'));
        $offer->setStatus('Sukurtas');

        $time = new \DateTime();

        $hash = md5($request->get('clientEmail') . $time->format('Y-m-d H:i:s'));

        $offer->setMd5($hash);
        $em->flush();
        $offerTemplates = $offer->getOfferTemplates();

        if ($active !== null) {
            foreach ($offerTemplates as $key => $value) {
                $index = $value->getTemplate()->getId();
                if (!in_array($index, $active)) {
                    $em->remove($value);
                    $em->flush();
                }
            }

            foreach ($active as $key => $value) {
                $exists = false;
                $template = $templRepo->find($key);
                foreach ($offerTemplates as $key2 => $value2) {
                    if ($value2->getTemplate() === $template) {
                        $exists = true;
                        dump($value2);
                        break;
                    }
                }
                if (!$exists) {
                    $template = $this->getDoctrine()->getRepository(Template::class)->find($key);
                    $templateOffer = new OfferTemplate();
                    $templateOffer->setOffer($offer)
                        ->setTemplate($template);
                    $templateOffer->setStatus("AddedToOffer");
                    $offer->addOfferTemplate($templateOffer);

                    $em->persist($templateOffer);
                    $em->persist($offer);
                }
            }
            $em->flush();
            return $this->redirectToRoute('admin');
            //return $this->redirectToRoute('sendmail', ['md5' => $offer->getMd5()]);
        }
    }

    public function reademail($md5)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Offer::class);
        $mrepo = $this->getDoctrine()->getRepository(Message::class);

        $offer = $repo->findByMd5($md5);

        if (!$offer) {
            return $this->render('admin/offer/error.html.twig', [
                'errorType' => 2
            ]);
        }

        if ($offer[0]->getStatus() != 'Parduota') {

            $offer[0]->setStatus('Peržiūrėtas');
            $em->persist($offer[0]);
            $em->flush();

            $messages = $mrepo->findByOfferId($offer[0]->getId());

            return $this->render('admin/offer/useroffer.html.twig', [
                'offer' => $offer[0],
                'messages' => $messages
            ]);
        } else {
            return $this->render('admin/offer/error.html.twig', [
                'errorType' => 1
            ]);
        }
    }

    /**
     * @Route("/sendrespond", name="sendrespond")
     */
    public function sendRespond(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Offer::class);
        $offer = $repo->find($request->get("offerId"));
        $message = new Message();
        $message->setText($request->get("msg"));
        $message->setOffer($offer);
        $message->setUsername($request->get("username"));
        $message->setDate(new \DateTime());

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('admin');
    }

    /**
     * @Route("/acceptoffer", name="acceptoffer")
     */
    public function acceptOffer(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $boughtTempl = new BoughtTemplate();
        $em->persist($boughtTempl);
        $offerTemplRepo = $em->getRepository(OfferTemplate::class);
        $acceptedId = $request->get('accept');

        $acceptedOT = $offerTemplRepo->find($acceptedId);

        $boughtOffer = $acceptedOT->getOffer();
        $boughtOffer->setStatus('Parduota');
        $boughtTemplate = new Template();
        $boughtTemplate->setPrice($acceptedOT->getTemplate()->getPrice());
        $boughtTemplate->setTitle($acceptedOT->getTemplate()->getTitle());
        $boughtTemplate->setStatus('Nupirkta');

        foreach ($acceptedOT->getTemplate()->getPositionTemplates() as $key => $value) {
            $remaining = $value->getPosition()->getRemaining();
            $use = $value->getCount();
            $value->getPosition()->setRemaining($remaining - $use);
            $valueclone = clone $value;
            $em->persist($valueclone);
            $boughtTemplate->addPositionTemplate($valueclone);
        }

        $em->persist($boughtOffer);
        $em->persist($boughtTemplate);

        $boughtTempl->setOffer($boughtOffer);
        $boughtTempl->setTemplate($boughtTemplate);

        $em->persist($boughtTempl);
        $em->flush();

        return $this->redirectToRoute('admin');
    }
}
