<?php

namespace App\Command\Cron;

use App\Entity\Market\Commodity;
use App\Entity\Market\UserCommodityNotification;
use App\Event\Commodity\Admin\CommodityAdminCreateEvent;
use App\Event\Commodity\Admin\CommodityAdminActivationEvent;
use App\Event\Commodity\Admin\CommodityAdminDeactivationEvent;
use App\Event\Commodity\Admin\CommodityAdminUpdateEvent;
use App\Event\Commodity\CommodityActiveToExpireEvent;
use App\Event\Commodity\CommodityCreateEvent;
use App\Event\Commodity\CommodityActivationEvent;
use App\Event\Commodity\CommodityDeactivationEvent;
use App\Event\Commodity\CommodityUpdateEvent;
use App\Service\Notification\SystemNotificationSender;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;


class UserCommodityNotificationWorkerCommand extends Command
{
    private const QUERY_LIMIT = 500;
    protected static $defaultName = 'app:userCommodityNotificationWorker';
    protected static $defaultDescription = 'This is cron job, which parses UserCommodityNotification and sends notifications to owners and customers';

    private static $commodityTypesUa = [
        'product' => 'продукт',
        'kit' => 'спільна пропозиція',
        'service' => 'послуга'
    ];

    private static $userTypesForTranslator = [
        'author',
        'co_author',
        'enjoyer'
    ];

    protected EntityManagerInterface $entityManager;
    protected SystemNotificationSender $systemNotificationSender;
    protected TranslatorInterface $translator;
    protected LoggerInterface $logger;

    public function __construct(
        EntityManagerInterface   $entityManager,
        SystemNotificationSender $systemNotificationSender,
        TranslatorInterface      $translator,
        LoggerInterface          $logger
    )
    {
        $this->systemNotificationSender = $systemNotificationSender;
        $this->entityManager = $entityManager;
        $this->translator = $translator;
        $this->logger = $logger;
        parent::__construct();
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $i = 0;
        foreach ($this->getUserCommodityNotificationQuery() as $item) {
            $i++;
            try {
                $this->processUserCommodityNotification($item);
            } catch (RuntimeException $exception) {
                $this->logger->error("Notification cron error: {$exception->getMessage()}");
            }
            if ($i % 50 === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        return Command::SUCCESS;
    }

    private function getUserCommodityNotificationQuery(): iterable
    {
        return $this->entityManager->getRepository(UserCommodityNotification::class)
            ->createQueryBuilder('ucn')
            ->select('ucn')
            ->where('ucn.notificationSent = false')
            ->orderBy('ucn.createdAt', 'ASC')
            ->setMaxResults(self::QUERY_LIMIT)
            ->getQuery()->toIterable();
    }

    private function processUserCommodityNotification(UserCommodityNotification $userCommodityNotification)
    {
        if ($this->checkUserIsCommodityOwner($userCommodityNotification)) {
            $this->sendSystemNotificationToCommodityOwner($userCommodityNotification);
        } else {
            $this->sendSystemNotification($userCommodityNotification);
        }
        $this->updateCommodityByEventType($userCommodityNotification);
        $userCommodityNotification->setUpdatedAt(new \DateTime());
        $userCommodityNotification->setNotificationSent(true);
        $this->entityManager->flush();
    }

    private function checkUserIsCommodityOwner(UserCommodityNotification $userCommodityNotification): bool
    {
        return $userCommodityNotification->getUser() === $userCommodityNotification->getCommodity()->getUser();
    }

    private function sendSystemNotificationToCommodityOwner(UserCommodityNotification $userCommodityNotification): void
    {
        $message = $this->translator->trans('cron.notification_sender.msg_parts_by_event.CommodityNotificationWorkerCommand.product.msgPart');
        $commodityType = $userCommodityNotification->getCommodity()->getCommodityType();
        if ($commodityType === 'спільна пропозиція') {
            $message = $this->translator->trans('cron.notification_sender.msg_parts_by_event.CommodityNotificationWorkerCommand.kit.msgPart');
        }
        if ($commodityType === 'послуга') {
            $message = $this->translator->trans('cron.notification_sender.msg_parts_by_event.CommodityNotificationWorkerCommand.service.msgPart');
        }
        $this->systemNotificationSender->sendSingleNotification([
            'receiver' => $userCommodityNotification->getCommodity()->getUser(),
            'title' => "Деактивація {$userCommodityNotification->getCommodity()->getTitle()}",
            'message' => "Шановний (-а) {$userCommodityNotification->getCommodity()->getUser()}, " .
                $message .
                ' ' . "{$userCommodityNotification->getCommodity()->getTitle()} було деактивовано."
        ]);
    }

    private function sendSystemNotification(UserCommodityNotification $userCommodityNotification)
    {
        if ($this->checkIfUserCoAuthorOfKit($userCommodityNotification)) {
            $data = $this->buildNotificationData($userCommodityNotification, 'co_author');
        } else {
            $data = $this->buildNotificationData($userCommodityNotification, 'enjoyer');
        }
        $data['receiver'] = $userCommodityNotification->getUser();
        $this->systemNotificationSender->sendSingleNotification($data);
    }

    private function checkIfUserCoAuthorOfKit(UserCommodityNotification $userCommodityNotification): bool
    {
        $flag = false;
        if ($userCommodityNotification->getCommodity()->getCommodityType() != Commodity::TYPE_KIT) {
            return false;
        }

        foreach ($userCommodityNotification->getCommodity()->getCommodities() as $commodity) {
            if ($commodity->getUser() === $userCommodityNotification->getUser()) {
                $flag = true;
                break;
            }
        }

        return $flag;
    }

    private function buildNotificationData(UserCommodityNotification $userCommodityNotification, string $userType): array
    {
        $this->validateUserType($userType);
        $data['title'] = $this->translator->trans('cron.notification_sender.' . $userType . '.title', [
            '%kit_name%' => $userCommodityNotification->getCommodity()->getTitle(),
            '%event%' => $this->translator->trans('cron.notification_sender.msg_parts_by_event.' .
                $this->getEventNameForTranslator($userCommodityNotification->getEventType()))
        ]);
        $data['message'] = $this->translator->trans('cron.notification_sender.' . $userType . '.message', [
            '%user%' => $userCommodityNotification->getUser()->getName(),
            '%kit_name%' => $userCommodityNotification->getCommodity()->getTitle(),
            '%event%' => $this->translator->trans('cron.notification_sender.msg_parts_by_event.' .
                $this->getEventNameForTranslator($userCommodityNotification->getEventType()))
        ]);

        return $data;
    }


    private function validateUserType(string $userType)
    {
        if (!in_array($userType, self::$userTypesForTranslator)) {
            throw new \InvalidArgumentException("Your property UserType not in allowed List.");
        }
    }

    private function getEventNameForTranslator(string $eventClassName): string
    {
        $data = explode('\\', $eventClassName);
        return array_pop($data);
    }

    private function updateCommodityByEventType(UserCommodityNotification $userCommodityNotification): void
    {
        //todo this place is for dispatching  new specific Events by EventType
        switch ($userCommodityNotification->getEventType()) {
            case CommodityDeactivationEvent::class:
            case CommodityAdminDeactivationEvent::class:
            case CommodityActivationEvent::class:
            case CommodityAdminActivationEvent::class:
            case CommodityCreateEvent::class:
            case CommodityUpdateEvent::class:
            case CommodityAdminCreateEvent::class:
            case CommodityAdminUpdateEvent::class:
            case CommodityActiveToExpireEvent::class:
                break;
        }
        $userCommodityNotification->getCommodity()->setUpdatedAt(new \DateTime());
    }
}
