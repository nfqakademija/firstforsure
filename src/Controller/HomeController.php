<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\OfferTemplate;
use App\Entity\Template;
use App\Event\OfferEvent;
use App\Models\TemplateStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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

        if (!$offer instanceof Offer) {
            throw new NotFoundHttpException("Pasiulymas nerastas");
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
    public function acceptOffer(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $offerTemplRepo = $em->getRepository(OfferTemplate::class);
        $acceptedId = $request->get('accept');

        $acceptedOT = $offerTemplRepo->find($acceptedId);
        $acceptedOT->setStatus("CHECKED");
        $em->persist($acceptedOT);

        $boughtOffer = $acceptedOT->getOffer();
        $boughtOffer->setStatus(Offer::ASSIGNED);

        $date = new \DateTime();
        $boughtOffer->setViewed($date->format('Y-m-d H:i:s'));

        $em->persist($boughtOffer);

        $message = (new Message)
            ->setText($request->get("msg"))
            ->setOffer($acceptedOT->getOffer())
            ->setUsername($request->get("username"))
            ->setDate(new \DateTime());

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('admin');
    }
}
