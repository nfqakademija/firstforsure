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
    public const CREATED = 'CREATED';
    public const ANSWERED = 'ANSWERED';
    public const ASSIGNED = 'ASSIGNED';
    public const CONFIRMED = 'CONFIRMED';
    public const VIEWED = 'VIEWED';
    public const SENT = 'SENT';
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $md5;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $clientEmail;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $clientName;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $viewed;

    /**
     * @var OfferTemplate[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\OfferTemplate", mappedBy="offer", orphanRemoval=true)
     */
    private $offerTemplates;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->offerTemplates = new ArrayCollection();
        $this->viewed = New \DateTime();
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Offer
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getViewed()
    {
        return $this->viewed;
    }

    /**
     * @param $viewed
     * @return Offer
     */
    public function setViewed($viewed): self
    {
        $this->viewed = $viewed;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param $status
     * @return Offer
     */
    public function setStatus($status): self
    {
        $this->status = $status;
        return $this;
    }


    public function templateList()
    {
        $templateList = '';
        foreach($this->offerTemplates as $key => $value)
        {
            $templateList .=  '"'.$value->getTemplate()->getTitle().'" ';
        }

        return $templateList;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param $message
     * @return Offer
     */
    public function setMessage($message): self
    {
        $this->message = $message;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getMd5()
    {
        return $this->md5;
    }

    /**
     * @param $md5
     * @return Offer
     */
    public function setMd5($md5): self
    {
        $this->md5 = $md5;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * @param $clientName
     * @return Offer
     */
    public function setClientName($clientName): self
    {
        $this->clientName = $clientName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     * @return Offer
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClientEmail()
    {
        return $this->clientEmail;
    }

    /**
     * @param $clientEmail
     * @return Offer
     */
    public function setClientEmail($clientEmail): self
    {
        $this->clientEmail = $clientEmail;
        return $this;
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