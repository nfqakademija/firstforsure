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

    /**
     * @Route("/readoffer/{md5}/choose/{id}", name="chooseoffer")
     */
    public function chooseOffer($md5, $id)
    {
        $offer = $this
            ->getDoctrine()
            ->getRepository(Offer::class)
            ->findByMd5($md5);
        $messages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findByOfferId($offer->getId());
        $offerTemplate = $this
            ->getDoctrine()
            ->getRepository(OfferTemplate::class)
            ->find($id);

        return $this->render('admin/offer/userofferchoose.html.twig', [
            'offerTemplate' => $offerTemplate,
            'offer' => $offer,
            'messages' => $messages,
            'selected' => 2
        ]);
    }
}
