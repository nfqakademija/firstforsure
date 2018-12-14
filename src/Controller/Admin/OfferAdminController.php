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
use App\Entity\Template;
use App\Models\TemplateStatus;
use App\Service\Admin\Offer\OfferManager;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

        return $this->render('admin/offer/edit.html.twig', [
            'messages' => [],
            'type' => "edit",
            'offer' => new Offer(),
            'id' => 0,
            'templateItems' => $templateItems
        ]);
    }

    public function editAction()
    {
        $offerRepo = $this->getDoctrine()->getRepository(Offer::class);
        $templRepo = $this->getDoctrine()->getRepository(Template::class);
        $msgRepo = $this->getDoctrine()->getRepository(Message::class);
        $id = $this->request->query->get('id');

        $activeOffer = $offerRepo->find($id);

        $messages = $msgRepo->findByOfferId($id);

        $activeOfferItems = $activeOffer->getOfferTemplates();
        $templateItems = $templRepo->findForSale('Nupirkta');

        foreach ($templateItems as $key => $value) {
            foreach ($activeOfferItems as $key2 => $value2) {
                if ($value2->getTemplate()->getId() === $value->getId()) {
                    $value->setActive(true);
                }
            }
        }
        return $this->render('admin/offer/edit.html.twig', [
            'type' => 'edit',
            'id' => $id,
            'offer' => $activeOffer,
            'templateItems' => $templateItems,
            'messages' => $messages
        ]);
    }

    public function sendAction()
    {
        $em = $this->getDoctrine()->getManager();

        $mailer = $this->get('mailer');
        //$transport = new \Swift_SmtpTransport('smtp.gmail.com',,'ssl')

        $id = $this->request->query->get('id');

        $repo = $this->getDoctrine()->getRepository(Offer::class);

        $offer = $repo->find($id);

        foreach ($offer->getOfferTemplates() as $key => $value) {
            $value->setStatus('Sent');
        }

        $offer->setStatus(Offer::SENT);
        $date = new \DateTime();
        $offer->setViewed($date->format('Y-m-d H:i:s'));

        $em->persist($offer);
        $em->flush();

        $message = (new \Swift_Message('Žalgirio reklamos pasiūlymas'))
            ->setFrom('zrvtzrvt@gmail.com')
            ->setTo($offer->getClientEmail())
            ->setBody(
                $this->renderView(
                // templates/emails/registration.html.twig
                    'admin/offer/mail.html.twig',
                    array('link' => $this->generateUrl('readoffer', ['md5' => $offer->getMd5()], UrlGeneratorInterface::ABSOLUTE_URL),
                        'offer' => $offer)
                //array('link' => '127.0.0.1:8000/readoffer/'.$offer->getMd5(), 'offer' => $offer)
                ),
                'text/html'
            )/*
             * If you also want to include a plaintext version of the message
            ->addPart(
                $this->renderView(
                    'emails/registration.txt.twig',
                    array('name' => $name)
                ),
                'text/plain'
            )
            */
        ;

        $mailer->send($message);
        return $this->redirect('/admin/?entity=Offer&action=list&menuIndex=4&submenuIndex=-1');
    }

    /**
     * @param Request $request
     * @param OfferManager $offerManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function makeOffer(Request $request, OfferManager $offerManager)
    {
        $offerManager->makeOffer($request, $this->getUser());
        return $this->redirect("/admin/?entity=Offer&action=list&menuIndex=4&submenuIndex=-1");
    }
}