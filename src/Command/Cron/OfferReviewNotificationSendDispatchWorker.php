<?php

namespace App\Command\Cron;

use DateTime;
use App\Entity\Market\Notification\BidOffer;
use App\Entity\Market\Notification\KitAgreementNotification;
use App\Entity\Market\Notification\Notification;
use App\Entity\Market\Notification\PriceOfferNotification;
use App\Event\Commodity\CommodityOfferReviewSendEvent;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OfferReviewNotificationSendDispatchWorker extends Command
{
    private const QUERY_LIMIT = 500;
    protected static $defaultName = 'app:offerReviewNotificationEventWorker';
    protected static $defaultDescription = 'This is cron job, which parses BidOffers and KitAgreementNotification and  dispatching OfferReviewNotificationSendEvent.';
    protected EntityManagerInterface $entityManager;
    protected LoggerInterface $logger;
    protected EventDispatcherInterface $eventDispatcher;

    public function __construct(
        EntityManagerInterface   $entityManager,
        LoggerInterface          $logger,
        EventDispatcherInterface $eventDispatcher
    )
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->eventDispatcher = $eventDispatcher;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(self::$defaultDescription);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->getNotificationQuery() as $notification) {
            try {
                $this->processNotification($notification);
            } catch (RuntimeException $exception) {
                $this->logger->error("CommodityEventWorker cron error: {$exception->getMessage()}");
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return Command::SUCCESS;
    }
    /**
     * @return Notification[]
     */
    private function getNotificationQuery(): iterable
    {
        $yesterday  = (new DateTime('now'))->modify('-1 day');
        $alias      = 'notification';
        $query      = $this->entityManager
            ->getRepository(Notification::class)
            ->createQueryBuilder($alias);

        $query
            ->andWhere(
                $query->expr()->orX(
                    $query->expr()->isInstanceOf($alias,BidOffer::class),
                    $query->expr()->isInstanceOf($alias,KitAgreementNotification::class),
                    $query->expr()->isInstanceOf($alias,PriceOfferNotification::class),
                )
            )
            ->andWhere(
                $query->expr()->orX(
                    $query->expr()->eq("$alias.offerReviewNotificationSent", 0),
                    $query->expr()->isNull("$alias.offerReviewNotificationSent")
                )
            )
            ->andWhere(
                $query->expr()->lte("$alias.createdAt", ':yesterday')
            )
            ->setParameter('yesterday', $yesterday)
            ->orderBy("$alias.createdAt",'ASC')
            ->setMaxResults(self::QUERY_LIMIT);

        return $query->getQuery()->getResult();
    }
    /**
     * @param Notification $notification
     *
     * @return void
     */
    private function processNotification(Notification $notification): void
    {
        $event = new CommodityOfferReviewSendEvent();
        $event->setNotification($notification);
        $this->eventDispatcher->dispatch($event);
    }
}
