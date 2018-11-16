<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PositionRepository")
 */
class Position
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $reach;

    /**
     * @ORM\Column(type="integer")
     */
    private $remaining;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxQuantity;

    private $count;

    /**
     * Position constructor.
     * @param $count
     */
    public function __construct()
    {
        $this->count = 0;
    }

    /**
     * @return mixed
     */
    public function getMaxQuantity()
    {
        return $this->maxQuantity;
    }

    /**
     * @param mixed $maxQuantity
     */
    public function setMaxQuantity($maxQuantity): void
    {
        $this->maxQuantity = $maxQuantity;
    }

    /**
     * @return mixed
     */
    public function getRemaining()
    {
        return $this->remaining;
    }

    /**
     * @param mixed $remaining
     */
    public function setRemaining($remaining): void
    {
        $this->remaining = $remaining;
    }

    /**
     * @return mixed
     */
    public function getCount()
    {
        return $this->count;
    }

    /**
     * @param mixed $count
     */
    public function setCount($count): void
    {
        $this->count = $count;
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getReach(): ?int
    {
        return $this->reach;
    }

    public function setReach(int $reach): self
    {
        $this->reach = $reach;

        return $this;
    }
}
