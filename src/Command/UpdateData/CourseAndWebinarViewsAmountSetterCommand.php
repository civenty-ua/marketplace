<?php

namespace App\Command\UpdateData;

use App\Entity\Course;
use App\Entity\Item;
use App\Entity\ItemRegistration;
use App\Entity\Partner;
use App\Entity\Webinar;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Throwable;

class CourseAndWebinarViewsAmountSetterCommand extends Command
{
    protected static $defaultName = 'app:import:course-webinar-views';

    protected const ITEM_CREATED = 'created';
    protected const ITEM_UPDATED = 'updated';
    protected const ITEM_FAILED = 'failed';
    protected const ITEM_SKIPPED = 'skipped';

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

        $this->setDescription('Application data import: course and webinar views amount update.
         Course views amount sets by logic OldUserCount+ViewsAmount+RegisteredUsers and for webinar logic is
         OldUserCount+ViewsAmount.')
            ->setHelp('Run Application data import process for course and webinar views amount update.
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
            self::ITEM_SKIPPED => 0,
        ];

        try {
            $output->writeln('Required data initializing...');
            $webinars = $this->getCoursesAndWebinarsWithOldUserCount();
        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln('Data processing...');
        foreach ($progressBar->iterate($webinars) as $item) {
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
        $output->writeln("Items failed: {$outputData[self::ITEM_SKIPPED]}");

        return Command::SUCCESS;
    }

    public function processItem($item)
    {
        if ($this->checkOldUserCountIsGreaterThenViewsAmount($item)) {
            $this->setFakeViewsAmount($item);
            $this->entityManager->flush();
            return self::ITEM_UPDATED;
        } else {
            return self::ITEM_SKIPPED;
        }
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This command should be run only once on production. Continue with this action? ', false);

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }
    }

    public function getCoursesAndWebinarsWithOldUserCount()
    {
        $dql = <<<DQL
            SELECT i FROM App\Entity\Item AS i
            WHERE  i.oldUserCount !=0 AND i.oldUserCount is not null
            DQL;
        $q = $this->entityManager->createQuery($dql);

        return $q->getResult();
    }

    public function setFakeViewsAmount($item)
    {
        if ($item instanceof Webinar) {
            $item->setViewsAmount($item->getOldUserCount() + $item->getViewsAmount());
        } elseif ($item instanceof Course) {
            $registeredUsersOnCourse = $this->entityManager->getRepository(ItemRegistration::class)->getCountUserInCourse($item);
            $item->setViewsAmount($registeredUsersOnCourse + $item->getViewsAmount() + $item->getOldUserCount());
        }

    }

    public function checkOldUserCountIsGreaterThenViewsAmount($item): bool
    {
        return ($item->getOldUserCount() !== 0 && ($item->getOldUserCount() > $item->getViewsAmount()));
    }
}