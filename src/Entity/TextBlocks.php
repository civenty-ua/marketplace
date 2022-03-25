<?php

namespace App\Entity;

use App\Repository\TextBlocksRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=TextBlocksRepository::class)
 */
class TextBlocks
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=TextType::class, inversedBy="id")
     * @ORM\JoinColumn(nullable=false)
     */
    private $TextTypeId;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $symbolCode;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $text;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $textDescrtiption;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTextTypeId(): ?TextType
    {
        return $this->TextTypeId;
    }

    public function setTextTypeId(?TextType $TextType): self
    {
        $this->TextTypeId = $TextType;

        return $this;
    }

    public function getSymbolCode(): ?string
    {
        return $this->symbolCode;
    }

    public function setSymbolCode(string $symbolCode): self
    {
        $this->symbolCode = $symbolCode;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getTextDescrtiption(): ?string
    {
        return $this->textDescrtiption;
    }

    public function setTextDescrtiption(string $textDescrtiption): self
    {
        $this->textDescrtiption = $textDescrtiption;

        return $this;
    }
}
