<?php

namespace App\Entity;

use App\Repository\TextTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TextTypeRepository::class)
 */
class TextType
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ORM\OneToMany(targetEntity=TextBlocks::class, mappedBy="TextTypeId")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $Name;

    public function __construct()
    {
        $this->tmp = new ArrayCollection();
    }

    public function __toString(): string
    {
       return (string) $this->getId().'. '.$this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->Name;
    }

    public function setName(string $Name): self
    {
        $this->Name = $Name;

        return $this;
    }
}
