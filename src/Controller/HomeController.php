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
use App\Service\Admin\Offer\OfferManager;
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
        return $this->render('home/index.html.twig');
    }


    public function reademail($md5)
    {
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
    public
    function sendRespond(Request $request)
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
    public
    function acceptOffer(Request $request)
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
