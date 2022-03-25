<?php
declare(strict_types = 1);

namespace App\Entity\Market;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Repository\Market\CommodityProductRepository;
use App\Entity\{
    District,
    Locality,
    Region,
    User,
};
/**
 * @ORM\Entity(repositoryClass=CommodityProductRepository::class)
 * @ORM\Table(name="market_commodity_product")
 * @Vich\Uploadable
 */
class CommodityProduct extends Commodity
{
    public const TYPE_BUY               = 'buy';
    public const TYPE_SELL              = 'sell';
    public const REQUIRED_USER_ROLES    = [
        User::ROLE_SALESMAN,
        User::ROLE_WHOLESALE_BUYER,
    ];
    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Category $category = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $image = null;

    /**
     * @Assert\File(
     *      maxSize="5M",
     *      mimeTypes={"image/png", "image/jpg", "image/jpeg", "image/pjpeg", "image/webp", "application/pdf", "application/x-pdf"}
     * )
     * @Vich\UploadableField(mapping="commodity_product_image", fileNameProperty="image")
     * @Ignore()
     */
    private ?File $imageFile = null;

    /**
     * @ORM\ManyToOne(targetEntity=Region::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Region $region = null;

    /**
     * @ORM\ManyToOne(targetEntity=District::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?District $district = null;

    /**
     * @ORM\ManyToOne(targetEntity=Locality::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private ?Locality $locality = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $type = '';

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isOrganic = null;

    /**
     * @param User|null $user
     */
    public function __construct(?User $user = null)
    {
        parent::__construct();
        if (!is_null($user)) {
            $this->setRegion($user->getRegion());
            $this->setDistrict($user->getDistrict());
            $this->setLocality($user->getLocality());
        }
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile)
    {
        $this->imageFile = $imageFile;
        if (null !== $imageFile) {
            $this->updatedAt = new DateTimeImmutable();
        }
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

    public function getDistrict(): ?District
    {
        return $this->district;
    }

    public function setDistrict(?District $district): self
    {
        $this->district = $district;

        return $this;
    }

    public function getLocality(): ?Locality
    {
        return $this->locality;
    }

    public function setLocality(?Locality $locality): self
    {
        $this->locality = $locality;

        return $this;
    }

    /**
     * Get available types.
     *
     * @return string[]                     Types set.
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_BUY,
            self::TYPE_SELL,
        ];
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIsOrganic(): ?bool
    {
        return $this->isOrganic ?? false;
    }

    public function getIsOrganicAndApproved(): ?bool
    {
        if (!$this->getIsOrganic()) {
            return false;
        }

        $certificates = $this->getUser()
            ? $this->getUser()->getUserProperty()->getUserCertificatesByFilter(true, true)
            : [];

        return count($certificates) > 0;
    }

    public function setIsOrganic(?bool $isOrganic): self
    {
        $this->isOrganic = $isOrganic;

        return $this;
    }

    public function getCommodityType():string
    {
        return Commodity::TYPE_PRODUCT;
    }
}
