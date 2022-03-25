<?php
declare(strict_types = 1);

namespace App\Entity\Market;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Repository\Market\UserPropertyRepository;
use App\Entity\{
    District,
    Locality,
    User,
};
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * @ORM\Entity(repositoryClass=UserPropertyRepository::class)
 * @ORM\Table(name="market_user_property")
 */
class UserProperty
{
    public const ALLOWED_COMMODITIES_DEFAULT        = 10;
    public const COMMODITY_DAYS_ACTIVITY_DEFAULT    = 30;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $companyName = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $address = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $facebookLink = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $instagramLink = null;

    /**
     * @ORM\ManyToOne(targetEntity=CompanyType::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?CompanyType $companyType = null;

    /**
     * @ORM\ManyToOne(targetEntity=LegalCompanyType::class)
     */
    private ?LegalCompanyType $legalCompanyType = null;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="userProperty")
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @App\Validator\YouTubeLink
     */
    private ?string $descriptionVideoLink = null;
    /**
     * @ORM\OneToMany(targetEntity=UserCertificate::class, mappedBy="userProperty", cascade={"persist", "remove"})
     * @Assert\Valid
     */
    private ?Collection $userCertificates = null;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    private ?int $commodityActiveToExtendedByDays = null;

    /**
     * @ORM\Column(type="integer",nullable=true)
     */
    private ?int $allowedAmountOfSellingCommodities = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isShowedModal;

    public function __construct()
    {
        $this->userCertificates = new ArrayCollection();
    }

    public function __toString(): string
    {
        if ($this->user !== null && $this->user->getName() !== null) {
            return $this->user->getName();
        }
        return '';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(?string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getFacebookLink(): ?string
    {
        return $this->facebookLink;
    }

    public function setFacebookLink(?string $facebookLink): self
    {
        $this->facebookLink = $facebookLink;

        return $this;
    }

    public function getInstagramLink(): ?string
    {
        return $this->instagramLink;
    }

    public function setInstagramLink(?string $instagramLink): self
    {
        $this->instagramLink = $instagramLink;

        return $this;
    }

    public function getCompanyType(): ?CompanyType
    {
        return $this->companyType;
    }

    public function setCompanyType(?CompanyType $companyType): self
    {
        $this->companyType = $companyType;

        return $this;
    }

    public function getLegalCompanyType(): ?LegalCompanyType
    {
        return $this->legalCompanyType;
    }

    public function setLegalCompanyType(?LegalCompanyType $legalCompanyType): self
    {
        $this->legalCompanyType = $legalCompanyType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setUserProperty(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getUserProperty() !== $this) {
            $user->setUserProperty($this);
        }

        $this->user = $user;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescriptionVideoLink(): ?string
    {
        return $this->descriptionVideoLink;
    }

    public function setDescriptionVideoLink(?string $descriptionVideoLink): self
    {
        $this->descriptionVideoLink = $descriptionVideoLink;

        return $this;
    }

    public function getVideoId(): ?string
    {
        if($this->getDescriptionVideoLink() !== null)
        {
            return Request::create($this->getDescriptionVideoLink())->query->get('v');
        }
        return '';
    }
    /**
     * @return Collection|UserCertificate[]
     */
    public function getUserCertificates(): Collection
    {
        return $this->userCertificates;
    }
    /**
     * @return Collection|UserCertificate[]
     */
    public function getUserCertificatesByFilter(
        ?bool   $isOrganic  = null,
        ?bool   $isApproved = null
    ): Collection {
        return $this
            ->getUserCertificates()
            ->filter(function(UserCertificate $certificate) use ($isOrganic, $isApproved): bool {
                if ($isOrganic === true && $isApproved === true) {
                    return $certificate->getIsEcology() && $certificate->getApproved();
                }
                if ($isOrganic === true && $isApproved === false) {
                    return $certificate->getIsEcology() && !$certificate->getApproved();
                }
                if ($isOrganic === true && is_null($isApproved)) {
                    return $certificate->getIsEcology();
                }

                if ($isOrganic === false) {
                    return !$certificate->getIsEcology();
                }

                return true;
            });
    }

    /**
     * @return Collection|UserCertificate[]
     */
    public function getUserCertificatesApproved(): Collection
    {
        $certificateApproved = new ArrayCollection();
        foreach ($this->getUserCertificates() as $certificate) {
            if ($certificate->getApproved()) {
                $certificateApproved->add($certificate);
            }
        }
        return $certificateApproved;
    }

    /**
     * @return Collection|UserCertificate[]
     */
    public function getUserCertificatesApprovedOrganic(): Collection
    {
        $certificateApproved = new ArrayCollection();
        foreach ($this->getUserCertificates() as $certificate) {
            if ($certificate->getApproved() and $certificate->getIsEcology()) {
                $certificateApproved->add($certificate);
            }
        }
        return $certificateApproved;
    }


    /**
     * @return Collection|UserCertificate[]
     */
    public function getUserCertificatesApprovedNotOrganic(): Collection
    {
        $certificateApproved = new ArrayCollection();
        foreach ($this->getUserCertificates() as $certificate) {
            if ($certificate->getApproved() and (!$certificate->getIsEcology() or is_null($certificate->getIsEcology()))) {
                $certificateApproved->add($certificate);
            }
        }
        return $certificateApproved;
    }

    /**
     * @return Collection|UserCertificate[]
     */
    public function getUserCertificatesNotApproved(): Collection
    {
        $certificateApproved = new ArrayCollection();
        foreach ($this->getUserCertificates() as $certificate) {
            if (!$certificate->getApproved()) {
                $certificateApproved->add($certificate);
            }
        }
        return $certificateApproved;
    }

    public function addUserCertificate(UserCertificate $userCertificate): self
    {
        if (!$this->userCertificates->contains($userCertificate)) {
            $this->userCertificates[] = $userCertificate;
            $userCertificate->setUserProperty($this);
        }

        return $this;
    }

    public function removeUserCertificate(UserCertificate $userCertificate): self
    {
        if ($this->userCertificates->removeElement($userCertificate)) {
            // set the owning side to null (unless already changed)
            if ($userCertificate->getUserProperty() === $this) {
                $userCertificate->setUserProperty(null);
            }
        }

        return $this;
    }

    public function getCommodityActiveToExtendedByDays(): int
    {
        return $this->commodityActiveToExtendedByDays ?? self::COMMODITY_DAYS_ACTIVITY_DEFAULT;
    }

    public function setCommodityActiveToExtendedByDays(?int $commodityActiveToExtendedByDays): void
    {
        $this->commodityActiveToExtendedByDays = $commodityActiveToExtendedByDays;
    }


    public function getAllowedAmountOfSellingCommodities(): int
    {
        return $this->allowedAmountOfSellingCommodities ?? self::ALLOWED_COMMODITIES_DEFAULT;
    }

    public function setAllowedAmountOfSellingCommodities(?int $allowedAmountOfSellingCommodities): void
    {
        $this->allowedAmountOfSellingCommodities = $allowedAmountOfSellingCommodities;
    }

    public function getIsShowedModal(): ?bool
    {
        return $this->isShowedModal;
    }

    public function setIsShowedModal(?bool $isShowedModal): self
    {
        $this->isShowedModal = $isShowedModal;

        return $this;
    }
}
