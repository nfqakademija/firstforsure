<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.17
 * Time: 14.37
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OfferTemplateRepository")
 */
class OfferTemplate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $reach;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $status;

    /**
     * @var Template
     * @ORM\ManyToOne(targetEntity="App\Entity\Template")
     * @ORM\JoinColumn(nullable=false)
     */
    private $template;

    /**
     * @var Offer
     * @ORM\ManyToOne(targetEntity="App\Entity\Offer", inversedBy="offerTemplates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $offer;

    /**
     * @var OfferPositionTemplate[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\OfferPositionTemplate", mappedBy="offerTemplate", orphanRemoval=true)
     */
    private $offerPositionTemplates;

    public function __construct()
    {
        $this->offerPositionTemplates = new ArrayCollection();
    }

    /**
     * @return Collection|OfferPositionTemplate[]
     */
    public function getOfferPositionTemplates(): Collection
    {
        return $this->offerPositionTemplates;
    }

    /**
     * @return mixed
     */
    public function getReach()
    {
        return $this->reach;
    }

    /**
     * @param mixed $reach
     */
    public function setReach($reach): void
    {
        $this->reach = $reach;
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }

    /**
     * @return Template
     */
    public function getTemplate(): Template
    {
        return $this->template;
    }

    /**
     * @param Template $template
     */
    public function setTemplate(Template $template): void
    {
        $this->template = $template;
    }

    /**
     * @return Offer|null
     */
    public function getOffer(): ?Offer
    {
        return $this->offer;
    }

    /**
     * @param Offer|null $offer
     *
     * @return OfferTemplate
     */
    public function setOffer(?Offer $offer): self
    {
        $this->offer = $offer;
        return $this;
    }

    /**
     * @param OfferPositionTemplate $positionTemplate
     *
     * @return OfferTemplate
     */
    public function addOfferPositionTemplate(OfferPositionTemplate $positionTemplate): self
    {
        if (!$this->offerPositionTemplates->contains($positionTemplate)) {
            $this->offerPositionTemplates[] = $positionTemplate;
            $positionTemplate->setOfferTemplate($this);
        }        return $this;
    }    /**
 * @param OfferPositionTemplate $positionTemplate
 *
 * @return Template
 */
    public function removeOfferPositionTemplate(OfferPositionTemplate $positionTemplate): self
    {
        if ($this->offerPositionTemplates->contains($positionTemplate)) {
            $this->offerPositionTemplates->removeElement($positionTemplate);
            // set the owning side to null (unless already changed)
            if ($positionTemplate->getOfferTemplate() === $this) {
                $positionTemplate->setOfferTemplate(null);
            }
        }        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getId() . ' - ' . $this->getId();
    }

    public function divide () {
        if ($this->getReach()==0){
            return 0;
        }
        return number_format($this->getPrice()/$this->getReach(),4);
    }

}