<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.17
 * Time: 15.06
 */

namespace App\Controller\Admin;

use App\Entity\Message;
use App\Entity\Offer;
use App\Entity\OfferTemplate;
use App\Entity\Order;
use App\Entity\Position;
use App\Entity\Template;
use App\Service\Admin\Offer\OfferManager;
use App\Service\Admin\Offer\OfferService;
use App\Service\MailerService;
use App\Service\OfferTemplate\OfferTemplateManager;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class OfferAdminController extends BaseAdminController
{
    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        $user = $this->getUser();
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            $dqlFilter = "entity.status != 'CONFIRMED'";
        } else {
            $dqlFilter = "entity.status != 'CONFIRMED' AND entity.user = " . $user->getId();
        }

        return $this->get('easyadmin.query_builder')->createListQueryBuilder(
            $this->entity,
            $sortField,
            $sortDirection,
            $dqlFilter);
    }

    public function newAction()
    {
        $repo = $this->getDoctrine()->getRepository(Template::class);
        $templateItems = $repo->findAll();

        $offer = new Offer();
        $offer->setStatus(Offer::CREATED);

        return $this->render('admin/offer/edit.html.twig', [
            'messages' => [],
            'offer' => $offer,
            'id' => 0,
            'templateItems' => $templateItems
        ]);
    }

    public function editAction()
    {
        $offerRepo = $this->getDoctrine()->getRepository(Offer::class);
        $msgRepo = $this->getDoctrine()->getRepository(Message::class);
        $posRepo = $this->getDoctrine()->getRepository(Position::class);

        $offerService = $this->get(OfferService::class);
        $id = $this->request->query->get('id');

        $activeOffer = $offerRepo->find($id);

        $templateItems = $offerService->setActiveTemplateItems(
            $this->getDoctrine()->getRepository(Template::class)->findAll(),
            $activeOffer->getOfferTemplates());

        $checkedOT = $this
            ->getDoctrine()
            ->getRepository(OfferTemplate::class)
            ->findCheckedOfferTemplate(OfferTemplate::CHECKED, $id);
        $offerService->setActivePositionItems($checkedOT,$posRepo->findAll());

        return $this->render('admin/offer/edit.html.twig', [
            'id' => $id,
            'offer' => $activeOffer,
            'templateItems' => $templateItems,
            'messages' => $msgRepo->findByOfferId($id),
            'positionTimeItems' => $posRepo->findByTime(true),
            'positionNoTimeItems' => $posRepo->findByTime(false)
        ]);
    }

    public function sendAction()
    {
        $mailer = $this->get('mailer');
        $mailerService = $this->get(MailerService::class);
        $offer = $this
            ->getDoctrine()
            ->getRepository(Offer::class)
            ->find($this->request->query->get('id'));

        $mailerService->changeStatuses($offer);
        $mailerService->send($mailer, $offer, 'readoffer');

        return $this->redirect('/admin/?entity=Offer&action=list&menuIndex=4&submenuIndex=-1');
    }

    /**
     * @param Request $request
     * @param OfferManager $offerManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function makeOffer(Request $request, OfferManager $offerManager)
    {
        $offerManager->makeOffer($request, $this->getUser(), Offer::CREATED);
        return $this->redirect("/admin/?entity=Offer&action=list&menuIndex=4&submenuIndex=-1");
    }

    /**
     * @param Request $request
     * @param MailerService $mailerService
     * @param OfferTemplateManager $offerTemplateManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @Route("/makeordertemplate", name="makeordertemplate")
     */
    public function makeOrderTemplate(
        Request $request,
        MailerService $mailerService,
        OfferTemplateManager $offerTemplateManager
    )
    {
        $mailer = $this->get('mailer');

        $mailerService->send($mailer, $offerTemplateManager->editTemplate($request), 'readorder');

        return $this->redirect("/admin/?entity=Order&action=list&menuIndex=3&submenuIndex=-1");
    }

    /**
     * @Route("/acceptoffer", name="acceptoffer")
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

        $em->persist($offer);
        $em->flush();

        return $this->redirect("/admin/?entity=Order&action=list&menuIndex=1&submenuIndex=-1");
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

    /**
     * @Route("/readorder/{md5}", name="readorder")
     */
    public function readorder($md5, OfferService $offerService)
    {
        $em = $this->getDoctrine()->getManager();

        $offer = $this
            ->getDoctrine()
            ->getRepository(Offer::class)
            ->findByMd5($md5);

        if (!$offer instanceof Offer) {
            throw new NotFoundHttpException("Pasiūlymas nerastas");
        }

        $offerService->changeOfferStatus($offer, Offer::VIEWED);
        $em->persist($offer);
        $offerTemplate = $this
            ->getDoctrine()
            ->getRepository(OfferTemplate::class)
            ->findCheckedOfferTemplate("CHECKED", $offer->getId());
        $em->flush();

        $messages = $this
            ->getDoctrine()
            ->getRepository(Message::class)
            ->findByOfferId($offer->getId());

        return $this->render('admin/order/userorder.html.twig', [
            'offer' => $offer,
            'offerTemplate' => $offerTemplate,
            'messages' => $messages,
            'selected' => 3
        ]);
    }
}