<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.17
 * Time: 14.34
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OfferRepository")
 */
class Offer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $clientEmail;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $clientName;

    /**
     * @var OfferTemplate[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\OfferTemplate", mappedBy="offer", orphanRemoval=true)
     */
    private $offerTemplates;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->offerTemplates = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @param mixed $clientName
     */
    public function setClientName($clientName): void
    {
        $this->clientName = $clientName;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getClientEmail()
    {
        return $this->clientEmail;
    }

    /**
     * @param mixed $clientEmail
     */
    public function setClientEmail($clientEmail): void
    {
        $this->clientEmail = $clientEmail;
    }

    /**
     * @return Collection|OfferTemplate[]
     */
    public function getOfferTemplates(): Collection
    {
        return $this->offerTemplates;
    }

    /**
     * @param OfferTemplate $offerTemplate
     *
     * @return Offer
     */
    public function addOfferTemplate(OfferTemplate $offerTemplate): self
    {
        if (!$this->offerTemplates->contains($offerTemplate)) {
            $this->offerTemplates[] = $offerTemplate;
            $offerTemplate->setOffer($this);
        }
        return $this;
    }

    /**
     * @param OfferTemplate $offerTemplate
     *
     * @return Offer
     */
    public function removePositionTemplate(OfferTemplate $offerTemplate): self
    {
        if ($this->offerTemplates->contains($offerTemplate)) {
            $this->offerTemplates->removeElement($offerTemplate);
            // set the owning side to null (unless already changed)
            if ($offerTemplate->getTemplate() === $this) {
                $offerTemplate->setTemplate(null);
            }
        }
        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getClientEmail() . ' - ' . $this->getId();
    }


}