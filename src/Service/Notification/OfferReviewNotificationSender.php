<?php

namespace App\Service\Notification;

use App\Entity\Market\Notification\OfferReview;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

class OfferReviewNotificationSender extends NotificationSenderService
{
    public function sendSingleNotification(array $data)
    {
        $this->validate(OfferReview::class,$data);
        $notification = new OfferReview();
        $this->setGeneralNotificationProperties($notification, $data);
        $notification->setParentNotification($data['parentNotification']);
        $notification->setUserToUserReview($data['userToUserReview']);
        $notification->setSenderIsRated(false);
        $this->entityManager->flush();
        $this->dispatchNotificationEvent($notification);
    }

    protected function requiredData(): array
    {
        return ['parentNotification','userToUserReview'];
    }
}