<?php

namespace App\Entity\Market\Notification;

use App\Entity\UserToUserReview;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\Market\Notification\OfferReviewRepository;
/**
 * @ORM\Entity(repositoryClass=OfferReviewRepository::class)
 * @ORM\Table(name="market_notification_offer_review")
 */
class OfferReview  extends Notification
{
    /**
     * @ORM\OneToOne(targetEntity=Notification::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private ?Notification $parentNotification;
    /**
     * @ORM\OneToOne(targetEntity=UserToUserReview::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private ?UserToUserReview $userToUserReview = null;

    /**
     * @ORM\Column(type="boolean",options={"default" : false})
     */
    private ?bool $senderIsRated;

    public function getParentNotification(): ?Notification
    {
        return $this->parentNotification;
    }

    public function setParentNotification(Notification $parentNotification): self
    {
        $this->parentNotification = $parentNotification;

        return $this;
    }

    public function getType(): string
    {
        return 'Відгук';
    }

    /**
     * @return UserToUserReview|null
     */
    public function getUserToUserReview(): ?UserToUserReview
    {
        return $this->userToUserReview;
    }

    /**
     * @param UserToUserReview|null $userToUserReview
     */
    public function setUserToUserReview(?UserToUserReview $userToUserReview): void
    {
        $this->userToUserReview = $userToUserReview;
    }

    public function getSenderIsRated(): ?bool
    {
        return $this->senderIsRated;
    }

    public function setSenderIsRated(?bool $senderIsRated): void
    {
        $this->senderIsRated = $senderIsRated;
    }
}
