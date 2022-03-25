<?php

namespace App\Command\UpdateData\Market;

use App\Entity\Market\UserProperty;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Throwable;

class UserPropertySetterCommand extends Command
{
    protected static $defaultName = 'app:import:user-property';

    protected const ITEM_UPDATED = 'updated';
    protected const ITEM_WITH_PROP = 'with userProperty';
    protected const ITEM_FAILED = 'failed';

    protected EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Application data import: user\'s userProperty field')
            ->setHelp('Run Application data import process for user userProperties.
             This command should be run only once on production');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputData = [
            self::ITEM_UPDATED => 0,
            self::ITEM_WITH_PROP => 0,
            self::ITEM_FAILED => 0,
        ];

        try {
            $output->writeln('Required data initializing...');
            $users = $this->entityManager->getRepository(User::class)->findAll();
        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln('Data processing...');
        foreach ($progressBar->iterate($users) as $item) {
            try {
                $result = $this->processItem($item);
                $outputData[$result]++;
            } catch (Throwable $exception) {
                $outputData[self::ITEM_FAILED]++;
                //TODO: make normal logging
                echo "ERROR: {$exception->getMessage()}\n";
                echo "ERROR: {$exception->getLine()}\n";
                echo "ERROR: {$exception->getFile()}\n";
            }
        }
        $this->entityManager->flush();
        $output->writeln('File data processing finished');
        $output->writeln("Items updated: {$outputData[self::ITEM_UPDATED]}");
        $output->writeln("Items with userProperty: {$outputData[self::ITEM_WITH_PROP]}");
        $output->writeln("Items failed: {$outputData[self::ITEM_FAILED]}");

        return Command::SUCCESS;
    }

    public function processItem($item): string
    {
        if (!$item->getUserProperty()) {
            $item->setUserProperty(new UserProperty());
            $this->entityManager->persist($item);
            return self::ITEM_UPDATED;
        }
        return self::ITEM_WITH_PROP;
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This command should be run only once on production. Continue with this action? ', false);

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }
    }
}