<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.15
 * Time: 14.32
 */

namespace App\Service;


use App\Entity\Offer;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class MailerService
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var OfferRepository
     */
    private $offerRepository;

    /**
     * @var Environment
     */
    private $template;

    /**
     * @var UrlGeneratorInterface
     */
    private $router;

    /**
     * MailerService constructor.
     * @param EntityManagerInterface $entityManager
     * @param OfferRepository $offerRepository
     * @param Environment $template
     * @param UrlGeneratorInterface $router
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        OfferRepository $offerRepository,
        Environment $template,
        UrlGeneratorInterface $router)
    {
        $this->entityManager = $entityManager;
        $this->offerRepository = $offerRepository;
        $this->template = $template;
        $this->router = $router;
    }


    /**
     * @param \Swift_Mailer $mailer
     * @param Offer $offer
     */
    public function send($mailer, $offer)
    {
        try {
        $message = (new \Swift_Message('Žalgirio reklamos pasiūlymas'))
            ->setFrom('zrvtzrvt@gmail.com')
            ->setTo($offer->getClientEmail())
            ->setBody(
                $this->template->render(
                    'admin/offer/mail.html.twig',
                    array('link' => $this->router->generate('readoffer', ['md5' => $offer->getMd5()], UrlGeneratorInterface::ABSOLUTE_URL),
                        'offer' => $offer)
                ),
                'text/html'
            );
            $mailer->send($message);
        }
        catch (\Exception $e){
        }
    }

    /**
     * @param Offer $offer
     */
    public function changeStatuses($offer): void
    {
        foreach ($offer->getOfferTemplates() as $offerTemplate) {
            $offerTemplate->setStatus('Sent');
        }

        $offer->setStatus(Offer::SENT);
        $offer->setViewed((new \DateTime())->format('Y-m-d H:i:s'));

        $this->entityManager->persist($offer);
        $this->entityManager->flush();
    }
}