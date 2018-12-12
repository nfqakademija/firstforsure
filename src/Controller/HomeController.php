<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\OfferTemplate;
use App\Entity\Position;
use App\Entity\PositionTemplate;
use App\Entity\Template;
use App\Event\OfferEvent;
use App\Models\OfferStatus;
use App\Repository\PositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Date;

class HomeController extends Controller
{
    public function index()
    {
        return $this->render('home/index.html.twig', [
            'positionItems' => $this->getDoctrine()->getRepository(Position::class)->findAll()
        ]);
    }

    public function new()
    {
        return $this->render('home/form.html.twig', [
            'positionItems' => $this->getDoctrine()->getRepository(Position::class)->findAll()
        ]);
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
        $offer->setUser($this->getUser());
        $date = new \DateTime();
        $offer->setViewed($date->format('Y-m-d H:i:s'));

        $em->persist($offer);

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
            return $this->redirect("/admin/?entity=Offer&action=list&menuIndex=4&submenuIndex=-1");
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

        if ($offer->getStatus() != 'Parduota') {

            $this->get('event_dispatcher')->dispatch(OfferEvent::class, (new OfferEvent())->setOffer($offer));

            $messages = $mrepo->findByOfferId($offer->getId());

            return $this->render('admin/offer/useroffer.html.twig', [
                'offer' => $offer,
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
        $boughtTempl = new Order();
        $em->persist($boughtTempl);
        $offerTemplRepo = $em->getRepository(OfferTemplate::class);
        $orderRep = $em->getRepository(Order::class);
        $acceptedId = $request->get('accept');

        $acceptedOT = $offerTemplRepo->find($acceptedId);

        $boughtOffer = $acceptedOT->getOffer();
        $boughtOffer->setStatus('Parduota');
        $boughtTemplate = new Template();
        $boughtTemplate->setPrice($acceptedOT->getTemplate()->getPrice());
        $boughtTemplate->setReach($acceptedOT->getTemplate()->getReach());
        $boughtTemplate->setTitle($acceptedOT->getTemplate()->getTitle());
        $boughtTemplate->setStatus('Nupirkta');

        foreach ($acceptedOT->getTemplate()->getPositionTemplates() as $value) {
            //$remaining = $value->getPosition()->getRemaining();
           // $use = $value->getCount();
            //$value->getPosition()->setRemaining($remaining - $use);
            $valueclone = clone $value;
            $em->persist($valueclone);
            $boughtTemplate->addPositionTemplate($valueclone);
        }

        $em->persist($boughtOffer);
        $em->persist($boughtTemplate);

        $boughtTempl->setOffer($boughtOffer);
        $boughtTempl->setTemplate($boughtTemplate);
        $boughtTempl->setStatus("Atsakytas");
        $date = new \DateTime();
        $boughtTempl->setViewed($date->format('Y-m-d H:i:s'));
        $boughtTempl->setUser($this->getUser());

        $em->persist($boughtTempl);

        $message = new Message();
        $message->setText($request->get("msg"));
        $message->setOrder($boughtTempl);
        $message->setUsername($request->get("username"));
        $message->setDate(new \DateTime());

        $em->persist($message);

        $em->flush();

        return $this->redirectToRoute('admin');
    }
}
