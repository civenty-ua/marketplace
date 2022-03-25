<?php

namespace App\Command\DeleteData;

use App\Entity\ItemRegistration;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Throwable;

class ItemRegistrationPurgeCommand extends Command
{
    protected static $defaultName = 'app:delete:item-registration';

    protected const ITEM_DELETED = 'deleted';

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

        $this->setDescription('Application data delete: purge ItemRegistration database table')
            ->setHelp('Run Application data delete process for ItemRegistration.
             This command should be run only once on production.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputData = [

            self::ITEM_DELETED => 0,
        ];

        try {
            $output->writeln('Required data initializing...');
            $itemRegistrations = $this->entityManager->getRepository(ItemRegistration::class)->findAll();
        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln('Data processing...');
        foreach ($progressBar->iterate($itemRegistrations) as $item) {
            try {
                $result = $this->processItem($item);
                $outputData[$result]++;
            } catch (Throwable $exception) {
                $outputData[self::ITEM_DELETED]++;
                //TODO: make normal logging
                echo "ERROR: {$exception->getMessage()}\n";
                echo "ERROR: {$exception->getLine()}\n";
                echo "ERROR: {$exception->getFile()}\n";
                print_r($item);
                print_r($exception->getTraceAsString());
            }
        }

        $output->writeln('File data processing finished');
        $output->writeln("Items deleted: {$outputData[self::ITEM_DELETED]}");

        return Command::SUCCESS;
    }

    public function processItem($item)
    {
        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return self::ITEM_DELETED;
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