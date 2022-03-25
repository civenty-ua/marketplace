<?php
declare(strict_types = 1);

namespace App\Entity\Market\Notification;

use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
use App\Repository\Market\Notification\NotificationRepository;
use App\Entity\User;
use App\Validator as AcmeAssert;
/**
 * @ORM\Entity(repositoryClass=NotificationRepository::class)
 * @ORM\Table(name="market_notification")
 * @ORM\InheritanceType("JOINED")
 * @ORM\DiscriminatorColumn(name="discr", type="string")
 * @ORM\DiscriminatorMap({"bid_offer" = "BidOffer", "kit_agreement_notification" = "KitAgreementNotification", "offer_review" = "OfferReview", "system_message" = "SystemMessage","price_offer" = "PriceOfferNotification"})
 * @AcmeAssert\NotificationTitleLengthConstraint()
 */
abstract class Notification
{
    use TimestampableTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notificationsSent")
     */
    private ?User $sender = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="notificationsReceived")
     * @ORM\JoinColumn(nullable=false)
     */
    private ?User $receiver = null;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private ?bool $isRead = null;

    /**
     * @ORM\Column(type="boolean",options={"default" : false})
     */
    private ?bool $isActive = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $message = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $title = null;

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
     * @ORM\Column(type="boolean", nullable=true,options={"default" : false})
     */
    private ?bool $isSoftDeleted = null;

    /**
     * @ORM\Column(type="boolean",options={"default" : false})
     */
    private ?bool $offerReviewNotificationSent;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function __toString() {
        $sender = $this->getSender();
        $senderName = '';
        if (!is_null($sender)) {
            $senderName = $sender->getName();
        }

        $receiver = $this->getReceiver();
        $receiverName = '';
        if (!is_null($receiver)) {
            $receiverName = $receiver->getName();
        }

        return $senderName . ' --> ' . $receiverName . '(' . $this->id . ')';
    }

    public function getSender(): ?User
    {
        return $this->sender;
    }

    public function setSender(?User $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getReceiver(): ?User
    {
        return $this->receiver;
    }

    public function setReceiver(?User $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getIsRead(): ?bool
    {
        return $this->isRead;
    }

    public function setIsRead(?bool $isRead): self
    {
        $this->isRead = $isRead;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): self
    {
        $this->message = $message;

        return $this;
    }

    abstract public function getType():string;

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }


    public function getOfferReviewNotificationSent(): bool
    {
        return $this->offerReviewNotificationSent;
    }

    /**
     * @param bool|null $offerReviewNotificationSent
     */
    public function setOfferReviewNotificationSent(bool $offerReviewNotificationSent): self
    {
        $this->offerReviewNotificationSent = $offerReviewNotificationSent;

        return $this;
    }


    public function getIsSoftDeleted():bool
    {
        return $this->isSoftDeleted;
    }


    public function setIsSoftDeleted(bool $isSoftDeleted): self
    {
        $this->isSoftDeleted = $isSoftDeleted;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
