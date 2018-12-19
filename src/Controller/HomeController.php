<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\OfferTemplate;
use App\Event\OfferEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return $this->redirect("/admin");
    }

    public function reademail($md5)
    {
        $offer = $this->getDoctrine()->getRepository(Offer::class)->findByMd5($md5);

        if (!$offer instanceof Offer) {
            throw new NotFoundHttpException("Pasiūlymas nerastas");
        }

        if ($offer->getStatus() != 'Parduota') {
            $this->get('event_dispatcher')->dispatch(OfferEvent::class, (new OfferEvent())->setOffer($offer));
            $messages = $this->getDoctrine()->getRepository(Message::class)->findByOfferId($offer->getId());

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
}
