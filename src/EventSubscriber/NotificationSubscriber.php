<?php

namespace App\EventSubscriber;

use App\Event\Notification\NotificationEvent;
use App\Service\MailSender\Provider\SwiftMailerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface  $em;
    private SwiftMailerProvider     $swiftMailerProvider;
    private UrlGeneratorInterface   $urlGenerator;

    public function __construct(
        EntityManagerInterface $em,
        SwiftMailerProvider    $swiftMailerProvider,
        UrlGeneratorInterface  $urlGenerator
    )
    {
        $this->em = $em;
        $this->swiftMailerProvider = $swiftMailerProvider;
        $this->urlGenerator = $urlGenerator;
    }

    public function onSendNotification(NotificationEvent $event): void
    {
        $this->em->persist($event->getNotification());
        $this->em->flush();

        $this->swiftMailerProvider->send(
            $event->getNotification()->getReceiver()->getEmail(),
            $event->getNotification()->getTitle() ?? substr($event->getNotification()->getMessage(),30),
            'email/market/notification/email-notification.html.twig',[
            'message' => $event->getNotification()->getMessage(),
                'notificationListLink' => $this->urlGenerator->generate('my_notifications'),
            ]
        );
        //Сюда добавляются все коммуникации с пользователями. Отправка уведомлений, писем и т.д.
        //todo RabbitMQ
    }

    public static function getSubscribedEvents(): array
    {
        return [
            NotificationEvent::class => ['onSendNotification']
        ];
    }
}