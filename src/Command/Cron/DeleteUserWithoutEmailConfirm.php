<?php

namespace App\Command\Cron;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Exception\RuntimeException;
use DateTime;

class DeleteUserWithoutEmailConfirm extends Command
{
    private const QUERY_LIMIT = 500;
    protected static $defaultName = 'app:deleteUserWithoutEmailConfirm';
    protected static string $defaultDescription = 'This is cron job, which parse and delete users without email confirm';
    protected const ITEM_DELETED = 'deleted';
    protected const ITEM_FAILED = 'failed';
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
        $outputData = [
            self::ITEM_DELETED => 0,
            self::ITEM_FAILED => 0,
        ];
        $output->writeln('Required data initializing...');
        foreach ($this->getUsersToDelete() as $user) {
            try {
                if (!$user->getNotificationsSent()->isEmpty() ||
                    !$user->getNotificationsReceived()->isEmpty() ||
                    !$user->getCommodities()->isEmpty() ||
                    !$user->getFavorites()->isEmpty()
                ) {
                    continue;
                }
                $this->entityManager->remove($user);
                $outputData[self::ITEM_DELETED]++;
            } catch (RuntimeException $exception) {
                $this->logger->error("DeleteUserWithoutEmailConfirm cron error: {$exception->getMessage()}");
                $output->writeln("ERROR: {$exception->getMessage()}");
                $outputData[self::ITEM_FAILED]++;
                return Command::FAILURE;
            }
        }
        $this->entityManager->flush();
        $this->entityManager->clear();

        $output->writeln('File data processing finished');
        $output->writeln("Users deleted: {$outputData[self::ITEM_DELETED]}");
        $output->writeln("Users failed: {$outputData[self::ITEM_FAILED]}");
        return Command::SUCCESS;
    }

    private function getUsersToDelete(): iterable
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->marketListFilter(null, null, ['roles' => [User::ROLE_USER], 'activity' => [false], 'created_at' => (new DateTime())->modify('-1 hour')])
            ->setMaxResults(self::QUERY_LIMIT)
            ->getQuery()
            ->getResult();
    }

}