<?php

namespace App\Entity\Market;

use App\Entity\User;
use App\Event\Commodity\Admin\CommodityAdminCreateEvent;
use App\Event\Commodity\Admin\CommodityAdminActivationEvent;
use App\Event\Commodity\Admin\CommodityAdminDeactivationEvent;
use App\Event\Commodity\Admin\CommodityAdminUpdateEvent;
use App\Event\Commodity\CommodityActiveToExpireEvent;
use App\Event\Commodity\CommodityCreateEvent;
use App\Event\Commodity\CommodityActivationEvent;
use App\Event\Commodity\CommodityDeactivationEvent;
use App\Event\Commodity\CommodityUpdateEvent;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use App\Repository\Market\UserCommodityNotificationRepository;

/**
 * @ORM\Entity(repositoryClass=UserCommodityNotificationRepository::class)
 */
class UserCommodityNotification
{
    public static array $eventTypes = [
        CommodityActiveToExpireEvent::class => 0,
        CommodityActivationEvent::class => 1,
        CommodityAdminActivationEvent::class => 2,
        CommodityCreateEvent::class => 3,
        CommodityUpdateEvent::class => 4,
        CommodityAdminCreateEvent::class => 5,
        CommodityAdminUpdateEvent::class => 6,
        CommodityDeactivationEvent::class => 7,
        CommodityAdminDeactivationEvent::class => 8
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $user;

    /**
     * @ORM\ManyToOne(targetEntity=Commodity::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Commodity $commodity;

    /**
     * @var DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     *
     */
    protected $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var DateTimeInterface
     */
    protected $updatedAt;

    /**
     * @ORM\Column(type="boolean",options={"default" : false})
     */
    private bool $notificationSent;

    /**
     * @ORM\Column(type="boolean",nullable=true)
     */
    private ?int $eventType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Updates createdAt and updatedAt timestamps.
     */
    public function updateTimestamps(): void
    {
        // Create a datetime with microseconds
        $dateTime = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)));

        if ($dateTime === false) {
            throw new ShouldNotHappenException();
        }

        $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

        if ($this->createdAt === null) {
            $this->createdAt = $dateTime;
            $this->updatedAt = $dateTime;
        }

        $this->updatedAt = $dateTime;
    }


    public function isNotificationSent(): bool
    {
        return $this->notificationSent;
    }

    public function setNotificationSent(bool $notificationSent): void
    {
        $this->notificationSent = $notificationSent;
    }

    /**
     * @return Commodity|null
     */
    public function getCommodity(): ?Commodity
    {
        return $this->commodity;
    }

    /**
     * @param Commodity|null $commodity
     */
    public function setCommodity(?Commodity $commodity): void
    {
        $this->commodity = $commodity;
    }

    public function getEventType(): ?string
    {
        $eventTypes =  array_flip(self::$eventTypes);
        return $eventTypes[$this->eventType];
    }


    public function setEventType(?string $eventType): void
    {
        $this->eventType = self::$eventTypes[$eventType];
    }
}
