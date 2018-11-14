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
     * Template constructor.
     */
    public function __construct()
    {
        $this->positionTemplates = new ArrayCollection();
    }

    /**
     * @return Collection|PositionTemplate[]
     */
    public function getPositionTemplates(): Collection
    {
//        dump($this->positionTemplates);
//        die();
        return $this->positionTemplates;
    }


    public function getId(): ?int
    {
        return $this->id;
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

}