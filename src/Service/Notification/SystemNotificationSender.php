<?php

namespace App\Service\Notification;

use App\Entity\User;
use App\Entity\Market\Notification\KitAgreementNotification;
use App\Entity\Market\Notification\SystemMessage;

class SystemNotificationSender extends NotificationSenderService
{
    public function sendSingleNotification(array $data)
    {
        $systemMessage = $this->buildSystemMessage($data);
        $this->dispatchNotificationEvent($systemMessage);
    }

    /**
     * @param KitAgreementNotification[] $allKitNotifications
     * @param string $kitAgreementTitle
     *
     * @return void
     */
    public function sendSystemNotificationsToKitAgreementParticipators(array $allKitNotifications, string $kitAgreementTitle): void
    {
        $kitParticipators = $this->getSenderAndReceiversFromNotifications($allKitNotifications);
        $this->createAndSendSystemNotifications($kitParticipators, $kitAgreementTitle);
    }
    /**
     * @param KitAgreementNotification[] $allKitNotifications
     * @param string $kitAgreementTitle
     *
     * @return void
     */
    public function kitApprovedButDeactivated(array $allKitNotifications, string $kitAgreementTitle): void
    {
        foreach ($this->getSenderAndReceiversFromNotifications($allKitNotifications) as $receiver) {
            $systemMessage = $this->buildSystemMessage([
                'receiver'  => $receiver,
                'title'     => "Активація спільної пропозиції - $kitAgreementTitle",
                'message'   =>
                    "Усі учасники схвалили створення спільної пропозиції $kitAgreementTitle, ".
                    'але вона поки що не активна. Власник спільної пропозиції повинен активувати її',
            ]);
            $this->dispatchNotificationEvent($systemMessage);
        }
    }
    /**
     * @param KitAgreementNotification[] $notifications
     *
     * @return User[]
     */
    protected function getSenderAndReceiversFromNotifications(array $notifications): array
    {
        $users = [];
        foreach ($notifications as $key => $notification) {
            if ($key == 0) {
                $users[] = $notification->getSender();
                if (count($notifications) == 1) {
                    $users[] = $notification->getReceiver();
                }
            } else {
                $users[] = $notification->getReceiver();
            }
        }

        return array_unique($users);
    }

    protected function createAndSendSystemNotifications(array $users, string $kitAgreementTitle): void
    {
        if (!empty($users)) {
            foreach ($users as $user) {
                $systemMessage = $this->buildSystemMessage([
                    'receiver' => $user,
                    'commodityTitle' => $kitAgreementTitle,
                    'title' => "Активація спільної пропозиції - {$kitAgreementTitle}"
                ]);
                $this->dispatchNotificationEvent($systemMessage);
            }
        }
    }

    protected function buildSystemMessage(array $data): SystemMessage
    {
        $this->validate(SystemMessage::class,$data);
        $systemMessage = new SystemMessage();
        $systemMessage->setIsSystem(true);
        $this->setGeneralNotificationProperties($systemMessage,$data);
        if (array_key_exists('commodityTitle', $data)) {
            $systemMessage->setMessage("Усі учасники схвалили створення спільної
                 пропозиції {$data['commodityTitle']} і тепер вона активна");
        }

        $this->entityManager->flush();

        return $systemMessage;
    }

    protected function requiredData(): array
    {
        return [];
    }
}