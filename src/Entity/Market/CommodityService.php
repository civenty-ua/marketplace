<?php
declare(strict_types = 1);

namespace App\Entity\Market;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Repository\Market\CommodityServiceRepository;
use App\Entity\User;
/**
 * @ORM\Entity(repositoryClass=CommodityServiceRepository::class)
 * @ORM\Table(name="market_commodity_service")
 * @Vich\Uploadable
 */
class CommodityService extends Commodity
{
    public const REQUIRED_USER_ROLES = [
        User::ROLE_SERVICE_PROVIDER,
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
     * @Vich\UploadableField(mapping="commodity_service_image", fileNameProperty="image")
     * @Ignore()
     */
    private ?File $imageFile = null;

    /**
     * @param User|null $user
     */
    public function __construct(?User $user = null)
    {
        parent::__construct();
        if (!is_null($user)) {
            if (method_exists($this, 'setRegion')) {
                $this->setRegion($user->getRegion());
            }
            if (method_exists($this, 'setDistrict')) {
                $this->setDistrict($user->getDistrict());
            }
            if (method_exists($this, 'setLocality')) {
                $this->setLocality($user->getLocality());
            }
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

    public function getCommodityType():string
    {
        return Commodity::TYPE_SERVICE;
    }
}
