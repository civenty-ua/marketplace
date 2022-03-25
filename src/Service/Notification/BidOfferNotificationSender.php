<?php

namespace App\Service\Notification;

use App\Entity\Market\Notification\BidOffer;

class BidOfferNotificationSender extends NotificationSenderService
{
    public function sendSingleNotification(array $data)
    {
        $notification =  $this->buildBidOfferNotification($data);
        $this->setGeneralNotificationProperties($notification, $data);
        $this->entityManager->flush();
        $this->dispatchNotificationEvent($notification);
    }

    private function buildBidOfferNotification(array $data): BidOffer
    {
        $this->validate(BidOffer::class,$data);
        $notification = new BidOffer();
        if (array_key_exists('name',$data)){
            $notification->setName($data['name']);
        }
        if (array_key_exists('phone',$data)){
            $notification->setName($data['phone']);
        }
        if (array_key_exists('email',$data)){
            $notification->setName($data['email']);
        }
        $notification->setCommodity($data['commodity']);
        $notification->setPrice($data['price']);
        $notification->setCommodity($data['commodity']);

        return $notification;
    }

    protected function requiredData(): array
    {
        return ['commodity','price'];
    }
}