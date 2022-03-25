<?php
declare(strict_types = 1);

namespace App\Command\Cron;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\{
    Command\Command,
    Input\InputInterface,
    Output\OutputInterface,
};
use Psr\{
    EventDispatcher\EventDispatcherInterface,
    Log\LoggerInterface,
};
use Symfony\Component\Console\Exception\RuntimeException;
use App\Event\Commodity\CommodityActiveToExpireEvent;
use App\Entity\Market\Commodity;
/**
 * Cron command. Commodities conditions controller.
 */
class CommodityEventWorkerCommand extends Command
{
    private const QUERY_LIMIT = 500;

    protected static $defaultName = 'app:commodityEventWorker';

    protected EntityManagerInterface    $entityManager;
    protected LoggerInterface           $logger;
    protected EventDispatcherInterface  $eventDispatcher;

    public function __construct(
        EntityManagerInterface      $entityManager,
        LoggerInterface             $logger,
        EventDispatcherInterface    $eventDispatcher
    ) {
        $this->entityManager    = $entityManager;
        $this->logger           = $logger;
        $this->eventDispatcher  = $eventDispatcher;

        parent::__construct();
    }
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('This is cron job, which parses expired commodity '.
                'and dispatching CommodityEvents. This cron job work for '.
                'UserCommodityNotificationWorker cron job.')
            ->setHelp('Run Application data import process for market products categories');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->getExpiredCommodities() as $commodity) {
            try {
                $this->processExpiredCommodity($commodity);
            } catch (RuntimeException $exception) {
                $this->logger->error("CommodityEventWorker cron error: {$exception->getMessage()}");
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        return Command::SUCCESS;
    }
    /**
     * Find and get commodities with expired active to dates.
     *
     * @return Commodity[]                  Commodities set.
     */
    private function getExpiredCommodities(): iterable
    {
        $alias          = 'commodity';
        $queryBuilder   = $this->entityManager
            ->getRepository(Commodity::class)
            ->createQueryBuilder($alias);

        $queryBuilder
            ->andWhere(
                $queryBuilder->expr()->eq("$alias.isActive", true)
            )
            ->andWhere(
                $queryBuilder->expr()->lt("$alias.activeTo", ':now')
            )
            ->setParameter('now', new DateTime('now'));

        return $queryBuilder
            ->setMaxResults(self::QUERY_LIMIT)
            ->getQuery()
            ->getResult();
    }
    /**
     * Deal with expired commodity.
     *
     * @param   Commodity $commodity        Commodity.
     *
     * @return  void
     */
    private function processExpiredCommodity(Commodity $commodity): void
    {
        $event = new CommodityActiveToExpireEvent();
        $event->setCommodity($commodity);
        $this->eventDispatcher->dispatch($event);
    }
}
