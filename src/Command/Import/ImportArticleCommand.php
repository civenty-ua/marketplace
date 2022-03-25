<?php

namespace App\Command\Import;

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
use App\Entity\Article;
/**
 * Import articles data class.
 */
class ImportArticleCommand extends AbstractImportCommand
{

    protected static $defaultName = 'app:import:article';

    private $succsessStory = "Історії успіху";
    private $ecoArticle = "Еко-технології";

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Application data import: articles')
            ->setHelp(
                'Run Application data import process for articles, ' .
                'setting data source file path'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'path to source file, absolute or from application root ' .
                '(for example: src/DataFixtures/sources/Import/Article/article.xlsx)'
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
        $createdAt = (string)($item[2] ?? '');
        $title = (string)($item[3] ?? '');
        $typePage = (string)($item[4] ?? '');
        $category = (string)($item[6] ?? '');
        $crops = explode(', ', (string)($item[7] ?? ''));
        $slug = (string)($item[8] ?? '');
        $previewText = (string)($item[9] ?? '');
        $needle = ['/_x005F/', '/_x000D_/'];
        $previewText = preg_replace($needle,'',$previewText);
        $fullText = (string)($item[10] ?? '');
        $fullText = preg_replace($needle,'',$fullText);
        $imageData = json_decode((string)($item[12] ?? ''), true);
        if ($imageData !== false and  isset($imageData['image_fulltext']) and !empty($imageData['image_fulltext'])) {
            $imagePath = $imageData['image_fulltext'];
        } elseif (isset($imageData['image_intro']) and !empty($imageData['image_intro']))  {
            $imagePath = $imageData['image_intro'];
        } else {
            $imagePath = "";
        }

        $metaText = (string)($item[13] ?? '');
        /** @var Article $article */
        if (isset($this->articles[$title])) {
            $article = $this->articles[$title];
        } else {
            $article = $this->buildNewArticle();
            $isNewItem = true;
        }

        $article
            ->translate($this->locale)
            ->setTitle(strlen($title) > 0 ? $title : null);
        $article
            ->translate($this->locale)
            ->setShort(strlen($previewText) > 0 ? $previewText : null);
        $article
            ->translate($this->locale)
            ->setContent(strlen($fullText) > 0 ? $fullText : '<p></p>');
        $article
            ->translate($this->locale)
            ->setKeywords(strlen($metaText) > 0 ? $metaText : null);
        $article->setSlug($slug);

        try {
            $createdAtPrepared = new DateTime($createdAt);
            $article->setCreatedAt($createdAtPrepared);
        } catch (Throwable $exception) {
            $article->setCreatedAt(new DateTime('now'));
        }

        try {
            $filePath = $imagesRoot . DIRECTORY_SEPARATOR . $imagePath;
            $file = new SplFileInfo($filePath);
            $fileCopy = $this->fileManager->copyEntityFile($file, $this->articlesImagesDirectory);
            $article->setImageName($fileCopy->getFilename());
        } catch (RuntimeException $exception) {
            $fileCopy = $this->fileManager->copyEntityFile(
                $this->defaultImage,
                $this->articlesImagesDirectory
            );
            $article->setImageName($fileCopy->getFilename());
        }

        if ($this->succsessStory != $category and $this->ecoArticle != $category) {
            $this->setCategory($article, $category);
        }

        foreach ($article->getCrops() as $existCrop) {
            $article->removeCrop($existCrop);
        }
        foreach ($crops as $crop) {
            $this->addCrop($article, $crop);
        }

        if (!$article->getTypePage()) {

            if ($this->succsessStory == $category) {
                $article->setTypePage($this->pagesTypes['success_stories']);
            } elseif ($this->ecoArticle == $category) {
                $article->setTypePage($this->pagesTypes['eco_articles']);
            }  else {
                if (trim($typePage) == 'Новини') {
                    $article->setTypePage($this->pagesTypes['news']);
                } else {
                    $article->setTypePage($this->pagesTypes['article']);
                }
            }
        }

        $this->entityManager->persist($article);
        $this->entityManager->flush();

        return $isNewItem ? self::ITEM_CREATED : self::ITEM_UPDATED;
    }
}