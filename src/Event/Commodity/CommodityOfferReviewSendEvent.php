<?php

namespace App\Event\Commodity;

use App\Entity\Market\Notification\Notification;
use Symfony\Contracts\EventDispatcher\Event;

class CommodityOfferReviewSendEvent extends Event
{
    private Notification $notification;

    /**
     * @return Notification
     */
    public function getNotification(): Notification
    {
        return $this->notification;
    }

    /**
     * @param Notification $notification
     */
    public function setNotification(Notification $notification): void
    {
        $this->notification = $notification;
    }
}
