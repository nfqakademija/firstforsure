<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.16
 * Time: 21.38
 */

namespace App\Controller;


use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\OfferTemplate;
use App\Service\Admin\Offer\OfferManager;
use App\Service\Admin\Offer\OfferService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OfferController extends Controller
{
    /**
     * @param Request $request
     * @param OfferManager $offerManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function makeOffer(Request $request, OfferManager $offerManager)
    {
        $offerManager->makeOffer($request, $this->getUser(), Offer::CREATED);
        return $this->redirect("/admin/?entity=Offer&action=list");
    }

    /**
     * @param Request $request
     * @param OfferService $offerService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function acceptOffer(Request $request, OfferService $offerService)
    {
        $em = $this->getDoctrine()->getManager();
        $acceptedId = $request->get('accept');
        $acceptedOT = $em->getRepository(OfferTemplate::class)->find($acceptedId);
        $offerService->acceptOffer($acceptedOT);

        $message = (new Message)
            ->setText($request->get("msg"))
            ->setOffer($acceptedOT->getOffer())
            ->setUsername($request->get("username"))
            ->setDate(new \DateTime());

        $em->persist($message);
        $em->flush();

        return $this->redirectToRoute('admin');
    }

    /**
     * @param Request $request
     * @param OfferService $offerService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function confirmOffer(Request $request, OfferService $offerService)
    {
        $em = $this->getDoctrine()->getManager();
        $offerTemplateRepository = $this->getDoctrine()->getRepository(OfferTemplate::class);
        $offer = $this->getDoctrine()->getRepository(Offer::class)->find($request->get('orderId'));
        $acceptedOT = $offerTemplateRepository->findCheckedOfferTemplate(OfferTemplate::CHECKED, $offer->getId());

        $offerService->changeOfferStatus($offer,Offer::CONFIRMED);
        $offerService->handleRemaining($acceptedOT);

        $em->flush();

        return $this->redirect("/admin/?entity=Order&action=list&menuIndex=1&submenuIndex=-1");
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