<?php

namespace App\Service\Notification;

use App\Entity\Market\Notification\KitAgreementNotification;

class KitAgreementNotificationSender extends NotificationSenderService
{
    public function sendSingleNotification(array $data)
    {
        $this->validate(KitAgreementNotification::class,$data);
        $notification = new KitAgreementNotification();
        $this->setGeneralNotificationProperties($notification, $data);
        $notification->setCommodity($data['commodity']);
        if (array_key_exists('name', $data)) {
            $notification->setName($data['name']);
        }
        if (array_key_exists('phone', $data)) {
            $notification->setName($data['phone']);
        }
        if (array_key_exists('status', $data)){
            $notification->setStatus($data['status']);
        }else{
            $notification->setStatus(KitAgreementNotification::STATUS_PENDING);
        }
        $this->entityManager->persist($data['commodity']);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
        $this->dispatchNotificationEvent($notification);
    }

    protected function requiredData(): array
    {
        return ['commodity'];
    }
}