<?php
declare(strict_types = 1);

namespace App\Entity\Market;

use App\Repository\Market\UserCertificateRepository;
use DateTime;
use DateTimeInterface;
use Serializable;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping as ORM;
use App\Validator as AppAssert;
use function in_array;
/**
 * @ORM\Entity(repositoryClass=UserCertificateRepository::class)
 * @ORM\Table(name="market_user_certificate")
 * @Vich\Uploadable
 * @AppAssert\FileEmpty()
 */
class UserCertificate implements Serializable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @Assert\File(
     *      maxSize="5M",
     *      mimeTypes={"image/png", "image/jpeg", "image/pjpeg", "application/pdf", "application/x-pdf", "image/jpg"}
     * )
     * @Vich\UploadableField(mapping="certificate_file", fileNameProperty="fileName", size="fileSize", originalName="originalName", mimeType="mimeType")
     * @Ignore()
     */
    private ?File $file = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    private ?string $fileName = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isEcology = null;

    /**
     * @ORM\ManyToOne(targetEntity=UserProperty::class, inversedBy="userCertificates")
     */
    private ?UserProperty $userProperty = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?DateTimeInterface $createdAt = null;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private ?int $fileSize = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $originalName = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $mimeType = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotBlank(message="role.empty_name")
     */
    private ?string $name = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $approved = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->originalName ?? '';
    }

    public function setFile(File $image = null): void
    {
        $this->file = $image;
        if ($image) {
            $this->createdAt = new DateTime('now');
        }
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }

    public function getIsEcology(): bool
    {
        return $this->isEcology ?? false;
    }

    public function setIsEcology(?bool $isEcology): self
    {
        $this->isEcology = $isEcology;

        return $this;
    }

    public function getUserProperty(): ?UserProperty
    {
        return $this->userProperty;
    }

    public function setUserProperty(?UserProperty $userProperty): self
    {
        $this->userProperty = $userProperty;

        return $this;
    }

    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    public function setFileSize(?int $fileSize): self
    {
        $this->fileSize = $fileSize;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function serialize()
    {
    }

    public function unserialize($serialized)
    {
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }


    public function getIsImage(): ?bool
    {
        if (in_array($this->mimeType, ['image/png', 'image/jpeg', 'image/pjpeg'])) {
            return true;
        }
        return false;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getApproved(): bool
    {
        return $this->approved ?? false;
    }

    public function setApproved(?bool $approved): self
    {
        $this->approved = $approved;

        return $this;
    }
}
