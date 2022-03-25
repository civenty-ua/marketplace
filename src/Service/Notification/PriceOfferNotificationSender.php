<?php

namespace App\Service\Notification;

use App\Entity\Market\Notification\PriceOfferNotification;

class PriceOfferNotificationSender extends NotificationSenderService
{
    public function sendSingleNotification(array $data)
    {
        $this->validate(PriceOfferNotification::class,$data);
        $notification = $this->buildPriceOfferNotification($data);
        $this->setGeneralNotificationProperties($notification,$data);
        $this->entityManager->flush();
        $this->dispatchNotificationEvent($notification);
    }

    private function buildPriceOfferNotification(array $data): PriceOfferNotification
    {
        $this->validate(PriceOfferNotification::class,$data);
        $notification = new PriceOfferNotification();

        if (array_key_exists('name',$data)){
            $notification->setName($data['name']);
        }
        if (array_key_exists('phone',$data)){
            $notification->setName($data['phone']);
        }
        if (array_key_exists('email',$data)){
            $notification->setName($data['email']);
        }
        $notification->setPrice($data['price']);
        $notification->setCommodity($data['commodity']);

        return $notification;
    }

    protected function requiredData(): array
    {
        return ['commodity','price'];
    }
}