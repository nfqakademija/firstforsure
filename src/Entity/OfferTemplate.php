<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.17
 * Time: 14.37
 */

namespace App\Entity;

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
     * @return string
     */
    public function __toString(): string
    {
        return $this->getId() . ' - ' . $this->getId();
    }

}