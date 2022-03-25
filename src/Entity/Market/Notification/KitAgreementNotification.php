<?php

namespace App\Entity\Market\Notification;


use App\Entity\Market\CommodityKit;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\Market\Notification\KitAgreementNotificationRepository;
use InvalidArgumentException;

/**
 * @ORM\Entity(repositoryClass=KitAgreementNotificationRepository::class)
 * @ORM\Table(name="market_notification_kit_agreement_notification")
 */
class KitAgreementNotification extends Notification
{
    //TODO composer dump-autoload after deletion entity
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_UPDATED_BY_OWNER = 'updated_by_owner';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $phone;

    /**
     * @ORM\ManyToOne(targetEntity=CommodityKit::class)
     */
    private ?CommodityKit $commodity;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private ?string $status;

    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_APPROVED,
            self::STATUS_EXPIRED,
            self::STATUS_PENDING,
            self::STATUS_REJECTED,
            self::STATUS_UPDATED_BY_OWNER
        ];
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCommodity(): ?CommodityKit
    {
        return $this->commodity;
    }

    public function setCommodity(?CommodityKit $commodity): self
    {
        $this->commodity = $commodity;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        if (!in_array($status, self::getAvailableStatuses())) {
            throw new InvalidArgumentException("unknown status $status");
        }

        $this->status = $status;

        return $this;
    }

    public function getType(): string
    {
        return 'Спільна пропозиція';
    }
}
