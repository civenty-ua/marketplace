<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\{
    Collection,
    ArrayCollection,
};
use App\Repository\DistrictRepository;
/**
 * @ORM\Entity(repositoryClass=DistrictRepository::class)
 */
class District
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
     * @ORM\ManyToOne(targetEntity=Region::class, inversedBy="districts")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Region $region;

    /**
     * @ORM\OneToMany(targetEntity=Locality::class, mappedBy="district", orphanRemoval=true)
     */
    private Collection $localities;

    public function __construct()
    {
        $this->localities = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->name;
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

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return Collection|Locality[]
     */
    public function getLocalities(): Collection
    {
        return $this->localities;
    }

    public function addLocality(Locality $locality): self
    {
        if (!$this->localities->contains($locality)) {
            $this->localities[] = $locality;
            $locality->setDistrict($this);
        }

        return $this;
    }

    public function removeLocality(Locality $locality): self
    {
        if ($this->localities->removeElement($locality)) {
            if ($locality->getDistrict() === $this) {
                $locality->setDistrict(null);
            }
        }

        return $this;
    }
}
