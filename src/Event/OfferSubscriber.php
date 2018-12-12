<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.12
 * Time: 19.44
 */

namespace App\Event;


use App\Models\OfferStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OfferSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * OfferSubscriber constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public static function getSubscribedEvents()
    {
        return [
            OfferEvent::class => 'onReadOffer'
        ];
    }

    public function onReadOffer(OfferEvent $event)
    {
        $offer = $event->getOffer();
        $offer->setStatus(OfferStatus::VIEWED); // perziuretas
        $date = new \DateTime();
        $offer->setViewed($date->format('Y-m-d H:i:s'));
        $this->entityManager->persist($offer);
        $this->entityManager->flush();
    }

}