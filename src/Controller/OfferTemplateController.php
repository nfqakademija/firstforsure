<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.16
 * Time: 21.41
 */

namespace App\Controller;


use App\Service\MailerService;
use App\Service\OfferTemplate\OfferTemplateManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class OfferTemplateController extends Controller
{
    /**
     * @param Request $request
     * @param MailerService $mailerService
     * @param OfferTemplateManager $offerTemplateManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function makeOfferTemplate(
        Request $request,
        MailerService $mailerService,
        OfferTemplateManager $offerTemplateManager
    )
    {
        $mailer = $this->get('mailer');

        $mailerService->send($mailer, $offerTemplateManager->editTemplate($request), 'user_read_assigned_offer');

        return $this->redirect('/admin/?entity=Offer&action=list');
    }
}