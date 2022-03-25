<?php

namespace App\Command\Import;

use App\Entity\Expert;
use App\Entity\ExpertTranslation;
use App\Entity\ExpertType;
use App\Entity\ExpertTypeTranslation;
use App\Entity\TagsTranslation;
use App\Service\FileManager\FileManagerInterface;
use App\Service\YoutubeClient;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Throwable;
use RuntimeException;
use SplFileInfo;
use DateTime;
use Symfony\Component\Console\{
    Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface,
    Helper\ProgressBar,
};
use function count;

/**
 * Import articles data class.
 */
class ImportExpertCommand extends AbstractImportCommand
{

    protected static $defaultName = 'app:import:expert';

    /**
     * Process item data.
     *
     * @param Logger $logger
     *
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface  $parameter,
        FileManagerInterface   $fileManager,
        YoutubeClient          $youtubeVideoDataReader
    )
    {
        parent::__construct($entityManager, $parameter, $fileManager, $youtubeVideoDataReader);

    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Application data import: experts')
            ->setHelp(
                'Run Application data import process for experts, ' .
                'setting data source file path'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'path to source file, absolute or from application root ' .
                '(for example: src/DataFixtures/sources/import/experts/experts.xlsx)'
            );
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
            $this->initializeData();

            if (count($this->pagesTypes) === 0) {
                throw new RuntimeException('no pages types exist, require at least one page type');
            }

            $output->writeln('File searching...');
            $file = $this->findDataProviderFile($input->getArgument('file'));
            $output->writeln('File reading...');
            $fileData = $this->parseDataProviderFile($file);
        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln('File data processing...');

        foreach ($progressBar->iterate($fileData) as $item) {
            try {
                $result = $this->processItem($item, $file->getPath());
                $outputData[$result]++;
            } catch (Throwable $exception) {
                $outputData[self::ITEM_FAILED]++;
                //TODO: make normal logging
                echo "ERROR: {$exception->getMessage()}\n";
            }
        }

        $output->writeln('File data processing finished');
        $output->writeln("Items created: {$outputData[self::ITEM_CREATED]}");
        $output->writeln("Items updated: {$outputData[self::ITEM_UPDATED]}");
        $output->writeln("Items failed: {$outputData[self::ITEM_FAILED]}");

        return Command::SUCCESS;
    }


    /**
     * Process item data.
     *
     * @param array $item Item data.
     * @param string $imagesRoot Images root path.
     *
     * @return  string                      Process result.
     */
    private function processItem(array $item, string $imagesRoot): string
    {
        $isNewItem = false;
        $data['name'] = trim((string)($item[0]) ?? '');
        $data['fullname'] = trim((string)($item[1] ?? ''));
        $data['email'] = (string)($item[2] ?? '');
        $data['positions'] = explode(', ', (string)($item[3] ?? ''));
        $data['bio'] = (string)($item[4] ?? '');
        $data['photo'] = (string)($item[5] ?? '');
        $data['tag'] = explode(', ', (string)($item[6] ?? ''));
        /** @var Expert $expert */

        $expert = $this->buildNewExpert($data['name'], $data['positions'], $data);

        $expert->setEmail($data['email']);
        $expert->setCreatedAt(new DateTime('now'));
        if (!is_null($data['photo']) && trim($data['photo']) == '+') {
            $imagePath = trim($data['name']) . '.jpg';
            try {
                $filePath = $imagesRoot . DIRECTORY_SEPARATOR . $imagePath;
                $file = new SplFileInfo($filePath);
                $fileCopy = $this->fileManager->copyEntityFile($file, $this->expertsImagesDirectory);
                $expert->setImage($fileCopy->getFilename());
            } catch (RuntimeException $exception) {
                $fileCopy = $this->fileManager->copyEntityFile(
                    $this->defaultImage,
                    $this->expertsImagesDirectory
                );
                $expert->setImage($fileCopy->getFilename());
            }
        }
        $this->entityManager->persist($expert);
        $this->entityManager->flush();
        if (!isset($this->experts[$data['name']])) {
            $this->experts[$data['name']] = $expert;
        }
        return $isNewItem ? self::ITEM_CREATED : self::ITEM_UPDATED;
    }

    /**
     * Build new expert.
     *
     * @param string $expertTitle Expert title.
     * @param string[] $positions Expert positions.
     *
     * @return  Expert                      Expert.
     */
    protected function buildNewExpert(string $expertTitle, array $positions = [], array $data = []): Expert
    {
        if (isset($this->experts[$data['name']])) {
            $expert = $this->experts[$data['name']];
        } else {
            $expert = new Expert();
        }
        $bio = $data['bio'];
        $tags = $data['tag'];
        $expertTitle = trim($data['name']);

        if (!isset($this->experts[$data['name']])) {
            $this->setItemLocaleChild($expert, new ExpertTranslation(), $this->locale);
        }

        $expert
            ->translate($this->locale)
            ->setName($this->mb_ucfirst(trim($expertTitle)));

        try {
            $file = $this->fileManager->copyEntityFile(
                $this->defaultImage,
                $this->expertsImagesDirectory
            );
            $expert->setImage($file->getFilename());
        } catch (RuntimeException $exception) {

        }


        if (!empty($positions)) {
            foreach ($positions as $position) {
                if (strlen($position) > 0) {
                    if (isset($this->expertsTypes[$this->mb_ucfirst($position)])) {
                        $expert->addExpertType($this->expertsTypes[$this->mb_ucfirst($position)]);
                    } else {
                        if (is_null($this->entityManager->getRepository(ExpertTypeTranslation::class)
                            ->findOneBy(['name' => $position]))) {
                            $expertType = $this->buildNewExpertType($position);
                            $this->expertsTypes[ucfirst($position)] = $expertType;
                            $expert->addExpertType($expertType);
                            $this->entityManager->persist($expertType);
                        }
                    }
                }
            }
        }

        if (!empty($tags)) {
            foreach ($tags as $tag) {
                if (strlen($tag) > 0) {
                    if (isset($this->tags[$this->mb_ucfirst(trim($tag))])) {
                        $expert->addTag($this->tags[$this->mb_ucfirst($tag)]);
                    } else {
                        $newTag = $this->buildNewTag($tag);
                        $expert->addTag($newTag);
                        $this->entityManager->persist($newTag);
                    }
                }
            }
        }

        if (!is_null($bio) && !empty($bio)) {
            $expert
                ->translate($this->locale)
                ->setContent($this->mb_ucfirst($bio));
        }

        return $expert;
    }
}