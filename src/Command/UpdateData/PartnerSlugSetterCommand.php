<?php

namespace App\Command\UpdateData;

use App\Entity\Partner;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Throwable;

class PartnerSlugSetterCommand extends Command
{
    protected static $defaultName = 'app:import:partner-slug';

    protected const ITEM_CREATED = 'created';
    protected const ITEM_UPDATED = 'updated';
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

        $this->setDescription('Application data import: partner\'s slug field')
            ->setHelp('Run Application data import process for partners slug.
             This command should be run only once on production');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputData = [
            self::ITEM_CREATED => 0,
            self::ITEM_UPDATED => 0,
            self::ITEM_FAILED => 0,
        ];

        try {
            $output->writeln('Required data initializing...');
            $partners = $this->entityManager->getRepository(Partner::class)->findAll();
        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln('Data processing...');
        foreach ($progressBar->iterate($partners) as $item) {
            try {
                $result = $this->processItem($item);
                $outputData[$result]++;
            } catch (Throwable $exception) {
                $outputData[self::ITEM_FAILED]++;
                //TODO: make normal logging
                echo "ERROR: {$exception->getMessage()}\n";
                echo "ERROR: {$exception->getLine()}\n";
                echo "ERROR: {$exception->getFile()}\n";
                print_r($item);
                print_r($exception->getTraceAsString());
            }
        }

        $output->writeln('File data processing finished');
        $output->writeln("Items updated: {$outputData[self::ITEM_UPDATED]}");
        $output->writeln("Items failed: {$outputData[self::ITEM_FAILED]}");

        return Command::SUCCESS;
    }

    public function processItem($item)
    {
        $slugify = new Slugify();

        $slug = $slugify->slugify($item->getTranslations()['uk']->getName());
        $slugCount = count($this->entityManager->getRepository(Partner::class)->findAllBySlug($slug));
        if ($slugCount > 0) $slug = $slug . '-' . $slugCount;
        $item->setSlug($slug);
        $this->entityManager->flush();

        return self::ITEM_UPDATED;
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