<?php

namespace App\Command\Import;

use Throwable;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\{
    Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface,
    Helper\ProgressBar,
};
use Cocur\Slugify\Slugify;
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpSpreadsheetDate;
/**
 * Import webinars data class.
 */
class ImportWebinarCommand extends AbstractImportCommand
{

    protected static $defaultName = 'app:import:webinar';

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Application data import: webinars')
            ->setHelp(
                'Run Application data import process for webinars, ' .
                'setting data source file path'
            )
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'path to source file, absolute or from application root ' .
                '(for example: src/DataFixtures/sources/Import/Webinar/webinar.xlsx)'
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
        $createdAt = (int)($item[1] ?? 0);
        $title = (string)($item[2] ?? '');
        $fileValue = (string)($item[3] ?? '');
        $content = (string)($item[4] ?? '');
        $partner = (string)($item[5] ?? '');
        $experts = explode(', ', (string)($item[6] ?? ''));
        $expertsPositions = explode('; ', (string)($item[8] ?? ''));
        $expertsPositions = array_map(function (string $value): array {
            return explode(', ', $value);
        }, $expertsPositions);
        $presentation = (string)($item[9] ?? '');
        $category = (string)($item[10] ?? '');
        $crops = explode(', ', (string)($item[11] ?? ''));
        $tags = explode(', ', (string)($item[12] ?? ''));
        $youtubeVideoId = (string)($item[13] ?? '');
        $rate = (float)($item[14] ?? 0);
        $rateUsersCount = (int)($item[15] ?? 0);

        if (isset($this->webinars[$title])) {
            $webinar = $this->webinars[$title];
        } else {
            $webinar = $this->buildNewWebinar();
            $isNewItem = true;
        }

        $webinar
            ->translate($this->locale)
            ->setTitle(strlen($title) > 0 ? $title : null);
        $webinar
            ->translate($this->locale)
            ->setContent(strlen($content) > 0 ? $content : null);

        $webinar
            ->translate($this->locale)
            ->setShort(strlen($content) > 0 ? $content : null);

        $slug = (new Slugify())->slugify($title);
        $webinar->setSlug($slug);

        $createdAtPrepared = PhpSpreadsheetDate::excelToDateTimeObject($createdAt);
        $webinar->setCreatedAt($createdAtPrepared);

        try {
            $filePath = $imagesRoot . DIRECTORY_SEPARATOR . $fileValue;
            $file = new SplFileInfo($filePath);
            $fileCopy = $this->fileManager->copyEntityFile($file, $this->webinarsImagesDirectory);
            $webinar->setImageName($fileCopy->getFilename());
        } catch (RuntimeException $exception) {

        }

        foreach ($webinar->getPartners() as $existPartner) {
            $webinar->removePartner($existPartner);
        }
        $this->addPartner($webinar, $partner);

        foreach ($webinar->getExperts() as $existExpert) {
            $webinar->removeExpert($existExpert);
        }
        foreach ($experts as $index => $expert) {
            $this->addExpert($webinar, $expert, $expertsPositions[$index] ?? []);
        }

        $presentationLink = explode('Презентація:', $presentation)[1] ?? '';
        $webinar->setPresentationLink(strlen($presentationLink) > 0 ? $presentationLink : null);

        $this->setCategory($webinar, $category);

        foreach ($webinar->getCrops() as $existCrop) {
            $webinar->removeCrop($existCrop);
        }
        foreach ($crops as $crop) {
            $this->addCrop($webinar, $crop);
        }

        foreach ($webinar->getTags() as $existTag) {
            $webinar->removeTag($existTag);
        }
        foreach ($tags as $tag) {
            $this->addWebinarTag($webinar, $tag);
        }

        $this->setVideo($webinar, $youtubeVideoId);

        $webinar->setRating($rate);
        $webinar->setOldUserCount($rateUsersCount);

        $this->entityManager->persist($webinar);
        $this->entityManager->flush();

        return $isNewItem ? self::ITEM_CREATED : self::ITEM_UPDATED;
    }
}