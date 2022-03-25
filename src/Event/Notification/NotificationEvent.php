<?php

namespace App\Event\Notification;

use App\Entity\Market\Notification\Notification;
use Symfony\Contracts\EventDispatcher\Event;

class NotificationEvent extends Event
{
    private Notification $notification;
    /**
     * Get item entity, if any.
     *
     * @return Notification|null                    Notification entity.
     */
    public function getNotification(): ?Notification
    {
        return $this->notification;
    }
    /**
     * Set/bind item entity
     *
     * @param   Notification|null $notification            Notification entity.
     *
     * @return  void
     */
    public function setNotification(?Notification $notification): void
    {
        $this->notification = $notification;
    }
}