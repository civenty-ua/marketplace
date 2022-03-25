<?php

namespace App\Event\Commodity;

use App\Entity\Market\Notification\KitAgreementNotification;
use Symfony\Contracts\EventDispatcher\Event;

class CommodityKitApprovingEvent extends Event
{
    private KitAgreementNotification $notification;

    /**
     * @return KitAgreementNotification
     */
    public function getNotification(): KitAgreementNotification
    {
        return $this->notification;
    }

    /**
     * @param KitAgreementNotification $notification
     */
    public function setNotification(KitAgreementNotification $notification): void
    {
        $this->notification = $notification;
    }
}
