<?php
/**
 * Created by PhpStorm.
 * User: osvaldas
 * Date: 18.11.12
 * Time: 19.14
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TemplateRepository")
 */

class Template
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
    private $title;

    /**
     * @var PositionTemplate[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\PositionTemplate", mappedBy="template", orphanRemoval=true)
     */
    private $positionTemplates;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="integer")
     */
    private $reach;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $status;

    private $active;

    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->positionTemplates = new ArrayCollection();
        $this->price = 0;
        $this->reach = 0;
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
     * @return mixed
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param mixed $active
     */
    public function setActive($active): void
    {
        $this->active = $active;
    }



    /**
     * @return Collection|PositionTemplate[]
     */
    public function getPositionTemplates(): Collection
    {
        return $this->positionTemplates;
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

    public function addPrice($price): self
    {
        $this->price += $price;
        return $this;
    }

    public function minusPrice($price): self
    {
        $this->price -= $price;
        return $this;
    }


    public function addReach($reach): self
    {
        $this->reach += $reach;
        return $this;
    }

    public function minusReach($reach): self
    {
        $this->reach -= $reach;
        return $this;
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param PositionTemplate $positionTemplate
     *
     * @return Template
     */
    public function addPositionTemplate(PositionTemplate $positionTemplate): self
    {
        if (!$this->positionTemplates->contains($positionTemplate)) {
            $this->positionTemplates[] = $positionTemplate;
            $positionTemplate->setTemplate($this);
        }        return $this;
    }    /**
 * @param PositionTemplate $positionTemplate
 *
 * @return Template
 */
    public function removePositionTemplate(PositionTemplate $positionTemplate): self
    {
        if ($this->positionTemplates->contains($positionTemplate)) {
            $this->positionTemplates->removeElement($positionTemplate);
            // set the owning side to null (unless already changed)
            if ($positionTemplate->getTemplate() === $this) {
                $positionTemplate->setTemplate(null);
            }
        }        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTitle() . ' - ' . $this->getId();
    }

    public function positionTitle()
    {
        $positions = '';
        $posTemplates = $this->getPositionTemplates();
        foreach($posTemplates as $key => $val)
        {
            $name = (string)$val->getPosition()->getName();
//            if(!strpos($positions, $name)) {
                $positions .= '"';
                $positions .= $name;
                $positions .= '" ';
//            }
        }
        return $positions;
    }

    public function divide () {
        if ($this->getReach()==0){
            return 0;
        }
        return number_format($this->getPrice()/$this->getReach(),4);
    }
}