<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.12
 * Time: 19.17
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass="App\Repository\PositionTemplateRepository")
 */
class PositionTemplate
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
     * @var Position
     * @ORM\ManyToOne(targetEntity="App\Entity\Position")
     * @ORM\JoinColumn(nullable=false)
     */
    private $position;


    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @var Template
     * @ORM\ManyToOne(targetEntity="App\Entity\Template", inversedBy="positionTemplates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $template;

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     * @return PositionTemplate
     */
    public function setPrice($price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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
     * @return Position|null
     */
    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    /**
     * @param Template|null $template
     *
     * @return PositionTemplate
     */
    public function setTemplate(?Template $template): self
    {
        $this->template = $template;
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