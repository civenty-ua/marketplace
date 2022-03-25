<?php

namespace App\Entity\DTO;

use App\Entity\Crop;
use App\Entity\District;
use App\Entity\Locality;
use App\Entity\Market\UserProperty;
use App\Entity\Region;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

class UserToFormBayer
{

    private $title;
    private $type;
    private $name;
    private $phone;
    private $email;
    /**
     * @ORM\ManyToOne(targetEntity=Region::class)
     */
    private $region;

    /**
     * @ORM\ManyToOne(targetEntity=District::class)
     */
    private ?District $district;

    /**
     * @ORM\ManyToOne(targetEntity=Locality::class)
     */
    private ?Locality $locality;

    /**
     * @ORM\ManyToMany(targetEntity=Crop::class, inversedBy="users")
     */
    private $crops;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $facebookLink;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $instagramLink;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $address;

    private  $em;

    public function __construct(?User $user = null, $em)
    {
        $this->crops = new ArrayCollection();
        if (!is_null($user)) {
            $this->setName($user->getName());
            $this->setPhone($user->getPhone());
            $this->setEmail($user->getEmail());
            $this->setRegion($user->getRegion());
            $this->setDistrict($user->getDistrict());
            $this->setLocality($user->getLocality());
            $this->crops = $user->getCrops();
            $userProperties = $user->getUserProperty();
            $this->setFacebookLink($userProperties->getFacebookLink());
            $this->setInstagramLink($userProperties->getInstagramLink());
            $this->setAddress($userProperties->getAddress());
        }
        $this->em =  $em;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }


    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): void
    {
        $this->region = $region;
    }

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): void
    {
        $this->district = $district;
    }

    public function getLocality(): ?Locality
    {
        return $this->locality;
    }

    public function setLocality(?Locality $locality): void
    {
        $this->locality = $locality;
    }

    /**
     * @return Collection|Crop[]
     */
    public function getCrops(): Collection
    {
        return $this->crops;
    }

    public function addCrop(Crop $crop): self
    {
        if (!$this->crops->contains($crop)) {
            $this->crops[] = $crop;
        }

        return $this;
    }

    public function removeCrop(Crop $crop): self
    {
        $this->crops->removeElement($crop);

        return $this;
    }

    public function getFacebookLink(): ?string
    {
        return $this->facebookLink;
    }

    public function setFacebookLink(?string $facebookLink): void
    {
        $this->facebookLink = $facebookLink;
    }

    public function getInstagramLink(): ?string
    {
        return $this->instagramLink;
    }

    public function setInstagramLink(?string $instagramLink): void
    {
        $this->instagramLink = $instagramLink;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    public function save(?User $user) {
        $user->setName($this->getName());
        $user->setRegion($this->getRegion());
        $user->setDistrict($this->getDistrict());
        $user->setLocality($this->getLocality());
        $user->setEmail($this->getEmail());
        $user->setPhone($this->getPhone());

        $crops = $this->getCrops();
        foreach ($user->getCrops() as $crop) {
            $user->removeCrop($crop);
        }
        foreach ($crops as $crop) {
            $user->addCrop($crop);
        }
        $user->setLocality($this->getLocality());
        $userProperties = $user->getUserProperty();
        $userProperties->setInstagramLink($this->getInstagramLink());
        $userProperties->setFacebookLink($this->getFacebookLink());
        $userProperties->setAddress($this->getAddress());
        $userProperties->setCompanyName($this->getTitle());

        $this->em->persist($userProperties);
        $this->em->persist($user);
        $this->em->flush();
    }

}