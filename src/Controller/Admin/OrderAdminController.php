<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.3
 * Time: 18.35
 */

namespace App\Controller\Admin;

use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\OfferPositionTemplate;
use App\Entity\OfferTemplate;
use App\Entity\Order;
use App\Entity\Position;
use App\Entity\PositionTemplate;
use App\Entity\Template;
use App\Service\Admin\Offer\OfferService;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderAdminController extends BaseAdminController
{
    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        $user = $this->getUser();
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            $dqlFilter = "entity.status = 'CONFIRMED'";
        } else {
            $dqlFilter = "entity.status = 'CONFIRMED' AND entity.user = " . $user->getId();
        }

        return $this->get('easyadmin.query_builder')->createListQueryBuilder(
            $this->entity,
            $sortField,
            $sortDirection,
            $dqlFilter);
    }

    /**
     * @Route("/readorder/{md5}", name="readorder")
     */
    public function readorder($md5)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Offer::class);
        $orepo = $this->getDoctrine()->getRepository(OfferTemplate::class);
        $mrepo = $this->getDoctrine()->getRepository(Message::class);

        $offer = $repo->findByMd5($md5);

        if (!$offer) {
            return $this->render('admin/offer/error.html.twig', [
                'errorType' => 2
            ]);
        }
        $offer->setStatus(Offer::VIEWED);
        $offerTemplate = $orepo->findCheckedOfferTemplate("CHECKED", $offer->getId());
        $offer->setViewed((new \DateTime())->format('Y-m-d H:i:s'));
        $em->persist($offer);
        $em->flush();

        $messages = $mrepo->findByOfferId($offer->getId());

        return $this->render('admin/order/userorder.html.twig', [
            'offer' => $offer,
            'offerTemplate' => $offerTemplate,
            'messages' => $messages,
            'selected' => 3
        ]);
    }

    /**
     *
     * @Route("/offer/client_response", name="client_response")
     */
    public function clientResponseSend(Request $request, OfferService $offerService)
    {
        $em = $this->getDoctrine()->getManager();
        $repo = $this->getDoctrine()->getRepository(Offer::class);
        $offer = $repo->find($request->get('orderId'));

        $offerService->changeOfferStatus($offer, Offer::ANSWERED);
        $em->persist($offer);

        $message = (new Message())
            ->setDate(new \DateTime())
            ->setText($request->get('msg'))
            ->setOffer($offer)
            ->setUsername($request->get('username'));

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute("admin");
    }
}