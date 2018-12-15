<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 */
class Message
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=1000)
     */
    private $text;

    /**
     * @var Offer
     * @ORM\ManyToOne(targetEntity="App\Entity\Offer")
     * @ORM\JoinColumn(nullable=false)
     */
    private $offer;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $username;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param $date
     * @return Message
     */
    public function setDate($date): self
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param $username
     * @return Message
     */
    public function setUsername($username): self
    {
        $this->username = $username;
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
     * @return Message
     */
    public function setOffer(Offer $offer): self
    {
        $this->offer = $offer;
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
     * @return Message
     */
    public function setId($id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param $text
     * @return Message
     */
    public function setText($text): self
    {
        $this->text = $text;
        return $this;
    }


}