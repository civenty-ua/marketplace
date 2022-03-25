<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\LocalityRepository;
/**
 * @ORM\Entity(repositoryClass=LocalityRepository::class)
 */
class Locality
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $name;

    /**
     * @ORM\ManyToOne(targetEntity=District::class, inversedBy="localities")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?District $district;

    public function getId(): ?int
    {
        return $this->id;
    }
    public function __toString()
    {
        return $this->name;
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

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): self
    {
        $this->district = $district;

        return $this;
    }
}
