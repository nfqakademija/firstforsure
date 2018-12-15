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
use App\Entity\Position;
use App\Entity\Template;
use App\Models\TemplateStatus;
use App\Service\Admin\Offer\OfferManager;
use App\Service\MailerService;
use App\Service\OfferTemplate\OfferTemplateManager;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OfferAdminController extends BaseAdminController
{
    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        $user = $this->getUser();
        if (in_array("ROLE_ADMIN", $user->getRoles())) {
            $dqlFilter = "entity.status != 'Parduota'";
        } else {
            $dqlFilter = "entity.status != 'Parduota' AND entity.user = " . $user->getId();
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
        $templateItems = $repo->findForSale(TemplateStatus::BOUGHT);

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
        $templRepo = $this->getDoctrine()->getRepository(Template::class);
        $msgRepo = $this->getDoctrine()->getRepository(Message::class);
        $otRepo = $this->getDoctrine()->getRepository(OfferTemplate::class);
        $id = $this->request->query->get('id');

        $activeOffer = $offerRepo->find($id);

        $messages = $msgRepo->findByOfferId($id);

        $activeOfferItems = $activeOffer->getOfferTemplates();
        $templateItems = $templRepo->findForSale('Nupirkta');

        foreach ($templateItems as $templateItem) {
            foreach ($activeOfferItems as $activeOfferItem) {
                if ($activeOfferItem->getTemplate()->getId() === $templateItem->getId()) {
                    $templateItem->setActive(true);
                }
            }
        }

        $posRepo = $this->getDoctrine()->getRepository(Position::class);
        $positionTimeItems = $posRepo->findByTime(true);
        $positionNoTimeItems = $posRepo->findByTime(false);

        $checkedOT = $otRepo->findCheckedOfferTemplate("CHECKED", $id);
        if($checkedOT) {
            $activePositionItems = $checkedOT->getOfferPositionTemplates();
            $positionItems = $posRepo->findAll();

            foreach ($positionItems as $value) {
                foreach ($activePositionItems as $value2) {
                    if ($value2->getPosition()->getId() === $value->getId()) {
                        $value->setCount($value2->getCount());
                        $value->setOfferPrice($value2->getPrice());
                    } else {
                        $value->setOfferPrice($value->getPrice());
                    }
                }
            }
        }

        return $this->render('admin/offer/edit.html.twig', [
            'id' => $id,
            'offer' => $activeOffer,
            'templateItems' => $templateItems,
            'messages' => $messages,
            'positionTimeItems' => $positionTimeItems,
            'positionNoTimeItems' => $positionNoTimeItems
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
        $mailerService->send($mailer, $offer);

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
     * @Route("/makeordertemplate", name="makeordertemplate")
     */
    public function makeOrderTemplate(Request $request, MailerService $mailerService, OfferTemplateManager $offerTemplateManager)
    {
        $mailer = $this->get('mailer');
        $offer = $offerTemplateManager->editTemplate($request);

        $mailerService->send($mailer, $offer);

        return $this->redirect("/admin/?entity=Order&action=list&menuIndex=3&submenuIndex=-1");
    }
}