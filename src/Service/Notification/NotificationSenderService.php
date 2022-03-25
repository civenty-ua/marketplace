<?php

namespace App\Service\Notification;

use App\Entity\Market\Notification\Notification;
use App\Event\Notification\NotificationEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

abstract class NotificationSenderService
{
    protected EntityManagerInterface $entityManager;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface   $entityManager,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    abstract public function sendSingleNotification(array $data);

    abstract protected function requiredData(): array;

    protected function validate($notificationType, $notificationData)
    {
        if (!empty($this->requiredData())) {
            foreach ($this->requiredData() as $field) {
                if (!array_key_exists($field, $notificationData)) {
                    throw new InvalidArgumentException("{$field}  key  is missing.
                    {$notificationType} Notification cannot be send without it.");
                }
            }
        }
    }

    protected function setGeneralNotificationProperties(Notification $notification, array $data)
    {
        $notification->setIsActive(true);
        $notification->setIsRead(false);
        $notification->setReceiver($data['receiver']);
        $notification->setCreatedAt(new \DateTime('now'));
        $notification->setUpdatedAt(new \DateTime('now'));
        $notification->setOfferReviewNotificationSent(false);
        $notification->setIsSoftDeleted(false);
        if (array_key_exists('title', $data)) {
            $notification->setTitle($data['title']);
        }
        if (array_key_exists('sender', $data)) {
            $notification->setSender($data['sender']);
        }
        if (array_key_exists('message', $data)) {
            $notification->setMessage($data['message']);
        }
    }

    protected function dispatchNotificationEvent(Notification $notification)
    {
        $notificationEvent = new NotificationEvent();
        $notificationEvent->setNotification($notification);
        $this->eventDispatcher->dispatch($notificationEvent);
    }
}