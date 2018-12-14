<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PositionTemplateRepository")
 */
class OfferPositionTemplate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", length=4)
     */
    private $count;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @var Position
     * @ORM\ManyToOne(targetEntity="App\Entity\Position")
     * @ORM\JoinColumn(nullable=false)
     */
    private $position;

    /**
     * @var Offer
     * @ORM\ManyToOne(targetEntity="App\Entity\Offer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $offer;

    /**
     * @var OfferTemplate
     * @ORM\ManyToOne(targetEntity="App\Entity\OfferTemplate", inversedBy="offerPositionTemplates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $offerTemplate;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    /**
     * @return Offer
     */
    public function getOffer(): Offer
    {
        return $this->offer;
    }

    /**
     * @param Offer $offer
     */
    public function setOffer(Offer $offer): void
    {
        $this->offer = $offer;
    }

    /**
     * @return Position|null
     */
    public function getOfferTemplate(): ?OfferTemplate
    {
        return $this->offerTemplate;
    }

    /**
     * @param OfferTemplate|null $template
     *
     * @return OfferPositionTemplate
     */
    public function setOfferTemplate(?OfferTemplate $template): self
    {
        $this->offerTemplate = $template;
        return $this;
    }

    /**
     * @return Position|null
     */
    public function getPosition(): ?Position
    {
        return $this->position;
    }

    /**
     * @param Position|null $position
     *
     * @return PositionTemplate
     */
    public function setPosition(?Position $position): self
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getPosition()->getName() . ' - ' . $this->getId();
    }
}