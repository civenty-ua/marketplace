<?php

namespace App\Command\DeleteData;

use App\Entity\DeadUrl;
use App\Service\DeadUrlBulkCommandService;
use App\Service\DeadUrlBulkService;
use App\Service\DeadUrlService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Throwable;

class DeadUrlPurgeByPatternCommand extends Command
{
    protected static $defaultName = 'app:delete:deadUrl';

    protected const ITEM_DELETED = 'deleted';

    protected const ITEM_PROCESSED = 'processed';

    protected const PATTERN_PART_CREATED = 'PATTERN_PART_CREATED';

    protected const PATTERN_INSERTED = 'patter_inserted';

    protected $patterns;

    protected $idsForDeleteQuery;

    protected $uniqueUriIds;

    protected $shouldBeEmptyArrayOfIds;

    protected EntityManagerInterface $entityManager;

    protected DeadUrlService $deadUrlService;

    protected DeadUrlBulkService $deadUrlBulkService;

    public function __construct(
        EntityManagerInterface $entityManager,
        DeadUrlService         $deadUrlService,
        DeadUrlBulkService     $deadUrlBulkService
    )
    {
        $this->entityManager = $entityManager;
        $this->deadUrlService = $deadUrlService;
        $this->deadUrlBulkService = $deadUrlBulkService;
        $this->patterns = [];
        $this->idsForDeleteQuery = [];
        $this->uniqueUriIds = [];
        $this->shouldBeEmptyArrayOfIds = [];
        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setDescription('Application data delete: purge DeadUrL database table and create patterns for redirect')
            ->setHelp('Run Application data delete process for DeadURL and create patterns by existing DeadRequests.
             This command should be run only once on production.');
    }

    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputData = [
            self::ITEM_DELETED => 0,
            self::ITEM_PROCESSED => 0,
            self::PATTERN_PART_CREATED => 0,
            self::PATTERN_INSERTED => 0,
        ];


        try {
            $output->writeln('Required data initializing...');
            $deadUrl = $this->getParsedDeadUrl()->toIterable();
        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln('Data processing...');
        foreach ($progressBar->iterate($deadUrl, 20) as $item) {
            try {
                $result = $this->processItem($item);
                $outputData[$result]++;
            } catch (Throwable $exception) {
                echo "ERROR: {$exception->getMessage()}\n";
                echo "ERROR: {$exception->getLine()}\n";
                echo "ERROR: {$exception->getFile()}\n";
            }
        }
        foreach ($progressBar->iterate($this->patterns, 20) as $pattern) {
            try {
                $this->processPattern($pattern);
            } catch (Throwable $exception) {
                echo "ERROR: {$exception->getMessage()}\n";
                echo "ERROR: {$exception->getLine()}\n";
                echo "ERROR: {$exception->getFile()}\n";
            }
        }
        $uniqueDeadUrlCount = count($this->uniqueUriIds);
        $idsForDel = count($this->idsForDeleteQuery);
        $shouldBeEmpty = count($this->shouldBeEmptyArrayOfIds);
        $this->deleteAllExceptUniqueUri();
        $uriPatterns = array_keys($this->patterns);
        foreach ($progressBar->iterate($uriPatterns, 20) as $pattern) {
            try {
                $deadUrl = $this->insertDeadUrlPatternInDb($pattern);
                if ($deadUrl){
                    $this->entityManager->persist($deadUrl);
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                    $outputData[self::PATTERN_INSERTED]++;
                }else {
                    $outputData[self::ITEM_PROCESSED]++;
                }

            } catch (Throwable $exception) {
                echo "ERROR: {$exception->getMessage()}\n";
                echo "ERROR: {$exception->getLine()}\n";
                echo "ERROR: {$exception->getFile()}\n";
            }
        }
        $output->writeln('File data processing finished');
        $output->writeln("Items deleted: {$outputData[self::ITEM_DELETED]}");
        $output->writeln("Items processed: {$outputData[self::ITEM_PROCESSED]}");
        $output->writeln("Pattern Parts created: {$outputData[self::PATTERN_PART_CREATED]}");
        $output->writeln("Patterns inserted: {$outputData[self::PATTERN_INSERTED]}");

        return Command::SUCCESS;
    }

    public function processItem($item)
    {
        if (true == str_ends_with($item['deadRequest'], '/*')
            || true == str_ends_with($item['deadRequest'], '*')
            || strlen($item['deadRequest']) <= 2) {
            return self::ITEM_PROCESSED;
        }


        $deadUrlBulkCommandService = DeadUrlBulkCommandService::getServiceAsClass($this->entityManager);
        $uriParts = $deadUrlBulkCommandService->parseUriParts($item['deadRequest']);

        $resultPatterns = $deadUrlBulkCommandService->createCorrectPatternsForSearchInRepository($item['deadRequest'], $uriParts);
        if (empty($resultPatterns)) {
            return self::ITEM_PROCESSED;
        }

        foreach ($resultPatterns as $resultPattern) {
            if (!array_key_exists($resultPattern, $this->patterns)) {
                $this->patterns[$resultPattern][] = $item['id'];
            } else {
                if (!in_array($item['id'], $this->patterns[$resultPattern])) {
                    $this->patterns[$resultPattern][] = $item['id'];
                }
            }
        }

        return self::PATTERN_PART_CREATED;
    }

    public function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');
        $question = new ConfirmationQuestion('This command should be run only once on production. Continue with this action? ', false);

        if (!$helper->ask($input, $output, $question)) {
            return Command::SUCCESS;
        }
    }

    public function getParsedDeadUrl()
    {
        return $this->entityManager->createQuery('Select du.id, du.deadRequest from App\Entity\DeadUrl du where du.isActive = false');
    }

    public function processPattern($pattern)
    {
        if (count($pattern) == 1){

                if (!in_array($pattern[0], $this->uniqueUriIds)) {
                    $this->uniqueUriIds[] = $pattern[0];
                } else {
                    if (!in_array($pattern[0], $this->idsForDeleteQuery)) {
                        $this->idsForDeleteQuery[] = $pattern[0];
                    } else {
                        $shouldBeEmptyArrayOfIds[] = $pattern[0];
                    }
                }

        }
    }
    public function deleteAllExceptUniqueUri()
    {
        $q = $this->entityManager->getRepository(DeadUrl::class)->createQueryBuilder('du');
        $q->delete()->where('du.id NOT IN (:idsOfUniqueUri)')
            ->setParameter('idsOfUniqueUri',$this->uniqueUriIds)
            ->andWhere('du.isActive = false')->getQuery()->execute();
    }

    public function insertDeadUrlPatternInDb($pattern):?DeadUrl
    {
        if(strlen($pattern)<=2) return null;

        $deadUrl = new DeadUrl();
        $deadUrl->setDeadRequest($pattern);
        $checkSum = crc32($pattern);
        $checkSum = current(unpack('l', pack('l', $checkSum)));
        $deadUrl->setCheckSum($checkSum);
        $deadUrl->setIsActive(false);
        $deadUrl->setCreatedAt();

        return $deadUrl;
    }
}