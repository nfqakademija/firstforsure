<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.12.12
 * Time: 19.39
 */

namespace App\Event;


use App\Entity\Offer;
use Symfony\Component\EventDispatcher\Event;

class OfferEvent extends Event
{
    /**
     * @var Offer $offer
     */
    private $offer;

    /**
     * @return Offer
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     * @return OfferEvent
     */
    public function setOffer(Offer $offer): self
    {
        $this->offer = $offer;
        return $this;
    }
}