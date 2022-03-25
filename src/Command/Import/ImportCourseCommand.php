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
use App\Entity\{
    Course,
    CoursePart,
    CourseTranslation,
};
use PhpOffice\PhpSpreadsheet\Shared\Date as PhpSpreadsheetDate;
/**
 * Import courses data class.
 */
class ImportCourseCommand extends AbstractImportCommand
{

    protected static $defaultName = 'app:import:course';

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
                echo "ERROR: {$exception->getLine()}\n";
                echo "ERROR: {$exception->getFile()}\n";
                print_r($item);
                print_r($exception->getTraceAsString());
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
        $partner = (string)($item[3] ?? '');
        $category = (string)($item[4] ?? '');
        $crops = explode(', ', (string)($item[5] ?? ''));
        $content = (string)($item[6] ?? '');
        $fileValue = (string)($item[7] ?? '');

        if (isset($this->courses[$title])) {
            $course = $this->courses[$title];
        } else {
            $course = $this->buildNewCourse();
            $isNewItem = true;
        }

        $course
            ->translate($this->locale)
            ->setTitle(strlen($title) > 0 ? $title : null);
        $course
            ->translate($this->locale)
            ->setContent(strlen($content) > 0 ? $content : null);

        $course
            ->translate($this->locale)
            ->setShort(strlen($content) > 0 ? $content : null);

        $slug = (new Slugify())->slugify($title);
        $course->setSlug($slug);

        $createdAtPrepared = PhpSpreadsheetDate::excelToDateTimeObject($createdAt);
        $course->setCreatedAt($createdAtPrepared);
        $course->setStartDate($createdAtPrepared);

        try {
            $filePath = $imagesRoot . DIRECTORY_SEPARATOR . $fileValue;
            $file = new SplFileInfo($filePath);
            $fileCopy = $this->fileManager->copyEntityFile($file, $this->coursesImagesDirectory);
            $course->setImageName($fileCopy->getFilename());
        } catch (RuntimeException $exception) {

        }

        foreach ($course->getPartners() as $existPartner) {
            $course->removePartner($existPartner);
        }
        $this->addPartner($course, $partner);

        $this->setCategory($course, $category);

        foreach ($course->getCrops() as $existCrop) {
            $course->removeCrop($existCrop);
        }
        foreach ($crops as $crop) {
            $this->addCrop($course, $crop);
        }

        $course->setRating(0);
        $course->setOldUserCount(0);

        $this->entityManager->persist($course);

        $step = 0;
        $allExperts =[];
        //Lesson reading
        while (isset($item[(int)(7 + $step * 4 + 1)]) and trim($item[(int)(7 + $step * 4 + 1)]) != "") {
            $titleLesson = trim($item[(int)(7 + $step * 4 + 1)]);
            $contentLesson = trim($item[(int)(7 + $step * 4 + 2)]);
            $videoLesson = trim($item[(int)(7 + $step * 4 + 3)]);
            $expertLesson = explode(',', trim($item[(int)(7 + $step * 4 + 4)]));
            $allExperts[] = $expertLesson;

            if (isset($this->lessons[$titleLesson])) {
                $lesson = $this->lessons[$titleLesson];
            } else {
                $lesson = $this->buildNewLesson();
                $isNewItem = true;
            }

            $lesson
                ->translate('en')
                ->setTitle(strlen($titleLesson) > 0 ? $titleLesson : null);
            $lesson
                ->translate('en')
                ->setContent(strlen($contentLesson) > 0 ? $contentLesson : null);

            $lesson
                ->translate('uk')
                ->setTitle(strlen($titleLesson) > 0 ? $titleLesson : null);
            $lesson
                ->translate('uk')
                ->setContent(strlen($contentLesson) > 0 ? $contentLesson : null);

            $this->setVideo($lesson, $videoLesson);

            $expert = $lesson->getExpert();
            if ($expert) {
                $lesson->setExpert(null);
            }
            foreach ($expertLesson as $expert) {
                $this->setExpert($lesson, $expert, $course);
            }

            $lesson->setCourse($course);
            $this->entityManager->persist($lesson);
            $step++;
        }
        $this->entityManager->persist($course);
        $this->entityManager->flush();

        return $isNewItem ? self::ITEM_CREATED : self::ITEM_UPDATED;
    }

    /**
     * Add course expert.
     *
     * @param CoursePart $entity Webinar.
     * @param string $title Expert title.
     * @param Course $course Expert positions.
     *
     * @return  void
     */
    protected function setExpert(
        CoursePart $entity,
        string $title,
        Course $course
    ): void
    {
        $expertTitle = $this->mb_ucfirst(trim($title));
        if (strlen($expertTitle) === 0) {
            return;
        }

        if (isset($this->experts[$expertTitle])) {
            $entity->setExpert($this->experts[$expertTitle]);
            $course->addExpert($this->experts[$expertTitle]);
        } else {
            $expert = $this->buildNewExpert($expertTitle, []);
            $this->experts[$expertTitle] = $expert;
            $entity->setExpert($expert);
            $course->addExpert($expert);
            $this->entityManager->persist($expert);
            $this->entityManager->persist($course);
        }
    }

    /**
     * Build new course.
     *
     * @return  Course                     Webinar.
     */
    protected function buildNewCourse(): Course
    {
        $course = new Course();
        $this->setItemLocaleChild($course, new CourseTranslation(), $this->locale);

        $course->setIsActive(true);

        return $course;
    }
}