<?php

namespace App\Command\Import;

use Throwable;
use InvalidArgumentException;
use RuntimeException;
use SplFileInfo;
use DateTime;
use DateInterval;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Contract\Entity\{
    TranslationInterface,
    TranslatableInterface,
};
use Cocur\Slugify\Slugify;
use App\Entity\{
    Article,
    ArticleTranslation,
    Category,
    CategoryTranslation,
    Course,
    Crop,
    CropTranslation,
    Expert,
    ExpertTranslation,
    ExpertType,
    ExpertTypeTranslation,
    Item,
    Lesson,
    LessonTranslation,
    Partner,
    PartnerTranslation,
    Tags,
    TagsTranslation,
    TypePage,
    VideoItem,
    VideoItemTranslation,
    Webinar,
    WebinarTranslation,
};
use App\Service\{
    YoutubeClient,
    FileManager\FileManagerInterface,
};
use PhpOffice\PhpSpreadsheet\{
    IOFactory,
    Exception as PhpSpreadsheetException,
};
/**
 * Import webinars data class.
 */
abstract class AbstractImportCommand extends Command
{
    private const FILE_ALLOWED_EXTENSIONS = [
        'xls',
        'xlsx',
    ];

    protected const ITEM_CREATED = 'created';
    protected const ITEM_UPDATED = 'updated';
    protected const ITEM_FAILED = 'failed';

    protected const DEFAULT_IMAGE =
        'public' . DIRECTORY_SEPARATOR .
        'images' . DIRECTORY_SEPARATOR .
        'item-default.png';

    protected EntityManagerInterface $entityManager;
    protected ParameterBagInterface $parameter;
    protected FileManagerInterface $fileManager;
    protected YoutubeClient $youtubeVideoDataReader;
    protected string $locale;
    protected Slugify $slugify;

    protected SplFileInfo $defaultImage;
    protected SplFileInfo $webinarsImagesDirectory;
    protected SplFileInfo $coursesImagesDirectory;
    protected SplFileInfo $partnersImagesDirectory;
    protected SplFileInfo $expertsImagesDirectory;
    protected SplFileInfo $categoriesImagesDirectory;
    protected SplFileInfo $cropsImagesDirectory;
    protected SplFileInfo $articlesImagesDirectory;
    /**
     * @var Webinar[] $webinars
     * @var Course[] $courses
     * @var Lesson[] $lessons
     * @var Partner[] $partners
     * @var Expert[] $experts
     * @var ExpertType[] $expertsTypes
     * @var Category[] $categories
     * @var Crop[] $crops
     * @var Tags[] $tags
     * @var TypePage[] $pagesTypes
     * @var VideoItem[] $videos
     * @var Article[] $articles
     */
    protected array $webinars = [];
    protected array $courses = [];
    protected array $lessons = [];
    protected array $partners = [];
    protected array $experts = [];
    protected array $expertsTypes = [];
    protected array $categories = [];
    protected array $crops = [];
    protected array $tags = [];
    protected array $pagesTypes = [];
    protected array $videos = [];
    protected array $articles = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        ParameterBagInterface $parameter,
        FileManagerInterface $fileManager,
        YoutubeClient $youtubeVideoDataReader
    ) {
        $this->entityManager = $entityManager;
        $this->parameter = $parameter;
        $this->fileManager = $fileManager;
        $this->youtubeVideoDataReader = $youtubeVideoDataReader;

        parent::__construct();
    }

    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();
    }

    /**
     * Run data initializing process.
     *
     * @return  void
     * @throws  RuntimeException            Process error.
     */
    protected function initializeData(): void
    {
        $this->locale = (string) $this
            ->parameter
            ->get('kernel.default_locale');

        if (strlen($this->locale) === 0) {
            throw new RuntimeException('default locale is not defined');
        }

        $this->slugify = new Slugify();
        $this->initializeRequiredFilesAndDirectories();
        $this->initializeRequiredDictionaries();

    }

    /**
     * Run data initializing process: files and directories.
     *
     * @return  void
     * @throws  RuntimeException            Process error.
     */
    protected function initializeRequiredFilesAndDirectories(): void
    {
        $projectDirectoryPath = $this->parameter->get('kernel.project_dir');
        $this->defaultImage = $this->findExistFile(
            $projectDirectoryPath . DIRECTORY_SEPARATOR .
            self::DEFAULT_IMAGE
        );

        $this->webinarsImagesDirectory = $this->findExistWritableDirectory(
            $projectDirectoryPath .
            $this->parameter->get('app.entity.files.webinar')
        );
        $this->coursesImagesDirectory = $this->findExistWritableDirectory(
            $projectDirectoryPath .
            $this->parameter->get('app.entity.files.course')
        );
        $this->partnersImagesDirectory = $this->findExistWritableDirectory(
            $projectDirectoryPath .
            $this->parameter->get('app.entity.files.partner')
        );
        $this->expertsImagesDirectory = $this->findExistWritableDirectory(
            $projectDirectoryPath .
            $this->parameter->get('app.entity.files.expert')
        );
        $this->categoriesImagesDirectory = $this->findExistWritableDirectory(
            $projectDirectoryPath .
            $this->parameter->get('app.entity.files.category.image')
        );
        $this->cropsImagesDirectory = $this->findExistWritableDirectory(
            $projectDirectoryPath .
            $this->parameter->get('app.entity.files.crop')
        );
        $this->articlesImagesDirectory = $this->findExistWritableDirectory(
            $projectDirectoryPath .
            $this->parameter->get('app.entity.files.article')
        );
    }

    /**
     * Run data initializing process: dictionaries.
     *
     * @return  void
     * @throws  RuntimeException            Process error.
     */
    protected function initializeRequiredDictionaries(): void
    {
        $dictionaries = [];

        foreach ([
                     'webinars' => Webinar::class,
                     'courses' => Course::class,
                     'lessons' => Lesson::class,
                     'partners' => Partner::class,
                     'experts' => Expert::class,
                     'expertsTypes' => ExpertType::class,
                     'categories' => Category::class,
                     'crops' => Crop::class,
                     'tags' => Tags::class,
                     'pagesTypes' => TypePage::class,
                     'videos' => VideoItem::class,
                     'article' => Article::class,
                 ] as $index => $entityClassName) {
            $dictionaries[$index] = $this
                ->entityManager
                ->getRepository($entityClassName)
                ->findAll();
        }

        /** @var Course $course */
        foreach ($dictionaries['courses'] as $course) {
            $title = $course->translate($this->locale)->getTitle() ?? '';
            if (strlen($title) > 0) {
                $this->courses[$this->mb_ucfirst($title)] = $course;
            }
        }

        /** @var Lesson $lesson */
        foreach ($dictionaries['lessons'] as $lesson) {
            $title = $lesson->translate($this->locale)->getTitle() ?? '';
            if (strlen($title) > 0) {
                $this->lessons[$this->mb_ucfirst($title)] = $lesson;
            }
        }

        /** @var Webinar $webinar */
        foreach ($dictionaries['webinars'] as $webinar) {
            $title = $webinar->translate($this->locale)->getTitle() ?? '';
            if (strlen($title) > 0) {
                $this->webinars[$this->mb_ucfirst($title)] = $webinar;
            }
        }
        /** @var Partner $webinar */
        foreach ($dictionaries['partners'] as $partner) {
            foreach ($partner->getTranslations() as $translation) {
                $title = $translation->getName() ?? '';
                if (strlen($title) > 0) {
                    $this->partners[$this->mb_ucfirst($title)] = $partner;
                }
            }
        }
        /** @var Expert $expert */
        foreach ($dictionaries['experts'] as $expert) {
            foreach ($expert->getTranslations() as $translation) {
                $title = $translation->getName() ?? '';
                if (strlen($title) > 0) {
                    $this->experts[$this->mb_ucfirst($title)] = $expert;
                }
            }
        }
        /** @var Category $category */
        foreach ($dictionaries['categories'] as $category) {
            foreach ($category->getTranslations() as $translation) {
                $title = $translation->getName() ?? '';
                if (strlen($title) > 0) {
                    $this->categories[$this->mb_ucfirst($title)] = $category;
                }
            }
        }
        /** @var Crop $crop */
        foreach ($dictionaries['crops'] as $crop) {
            foreach ($crop->getTranslations() as $translation) {
                $title = $this->mb_ucfirst($translation->getName()) ?? '';
                if (strlen($title) > 0) {
                    $this->crops[$title] = $crop;
                }
            }
        }
        /** @var Tags $tag */
        foreach ($dictionaries['tags'] as $tag) {
            foreach ($tag->getTranslations() as $translation) {
                $title = $translation->getName() ?? '';
                if (strlen($title) > 0) {
                    $this->tags[$this->mb_ucfirst($title)] = $tag;
                }
            }
        }
        /** @var TypePage $pageType */
        foreach ($dictionaries['pagesTypes'] as $pageType) {
            foreach ($pageType->getTranslations() as $translation) {
                $title = $translation->getName() ?? '';
                if (strlen($title) > 0) {
                    $this->pagesTypes[$pageType->getCode()] = $pageType;
                }
            }
        }
        /** @var VideoItem $video */
        foreach ($dictionaries['videos'] as $video) {
            $videoId = $video->getVideoId();
            if (strlen($videoId) > 0) {
                $this->videos[$videoId] = $video;
            }
        }
        /** @var Article $article */
        foreach ($dictionaries['article'] as $article) {
            $title = $article->translate($this->locale)->getTitle() ?? '';
            if (strlen($title) > 0) {
                $this->articles[$this->mb_ucfirst($title)] = $article;
            }
        }
    }

    function mb_ucfirst($str, $encoding='UTF-8')
    {
        $str = mb_ereg_replace('^[\ ]+', '', $str);
        $str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
            mb_substr($str, 1, mb_strlen($str), $encoding);
        return $str;
    }

    /**
     * Find data provider file by "file" argument value.
     *
     * @param string $value "file" argument value.
     *
     * @return  SplFileInfo                 File.
     * @throws  RuntimeException            File is not valid.
     */
    protected function findDataProviderFile(string $value): SplFileInfo
    {
        $filePath = $value === DIRECTORY_SEPARATOR
            ? $value
            : $this->parameter->get('kernel.project_dir') . DIRECTORY_SEPARATOR . $value;
        $file = $this->findExistFile($filePath);

        if (!in_array($file->getExtension(), self::FILE_ALLOWED_EXTENSIONS)) {
            $allowedExtensions = implode(', ', self::FILE_ALLOWED_EXTENSIONS);
            throw new RuntimeException(
                'unsupported file extension, ' .
                "allowed extensions are $allowedExtensions"
            );
        }

        return $file;
    }

    /**
     * Parse data provider file and extract its data.
     *
     * @param SplFileInfo $file File.
     *
     * @return  array[]                     File data.
     * @throws  RuntimeException            Any parse error.
     */
    protected function parseDataProviderFile(SplFileInfo $file): array
    {
        try {
            $fileExtension = $this->mb_ucfirst(strtolower($file->getExtension()));
            $reader = IOFactory::createReader($fileExtension);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($file->getPathname());
            $sheet = $spreadsheet->getSheet(0);
            $data = $sheet->toArray();

            unset($data[0]);
            return $data;
        } catch (PhpSpreadsheetException $exception) {
            throw new RuntimeException(
                "data parsing failed with error: {$exception->getMessage()}"
            );
        }
    }

    /**
     * Build new webinar.
     *
     * @return  Webinar                     Webinar.
     */
    protected function buildNewWebinar(): Webinar
    {
        $webinar = new Webinar();
        $this->setItemLocaleChild($webinar, new WebinarTranslation(), $this->locale);
        $webinar->setIsActive(true);
        $webinar->setUsePartnerApiKeys(false);

        return $webinar;
    }

    /**
     * Build new lesson.
     *
     * @return  Lesson                     Webinar.
     */
    protected function buildNewLesson(): Lesson
    {
        $lesson = new Lesson();
        $this->setItemLocaleChild($lesson, new LessonTranslation(), $this->locale);
        $lesson->setActive(true);

        return $lesson;
    }

    /**
     * Add webinar partner.
     *
     * @param Item $entity Webinar.
     * @param string $title Partner title.
     *
     * @return  void
     */
    protected function addPartner(Item $entity, string $title): void
    {
        $partnerTitle = trim($title);
        if (strlen($partnerTitle) === 0) {
            return;
        }

        if (isset($this->partners[$this->mb_ucfirst($partnerTitle)])) {
            $entity->addPartner($this->partners[$this->mb_ucfirst($partnerTitle)]);
        } else {
            $partner = $this->buildNewPartner($partnerTitle);
            $this->partners[$this->mb_ucfirst($partnerTitle)] = $partner;
            $entity->addPartner($partner);
            $this->entityManager->persist($partner);
        }
    }

    /**
     * Build new partner.
     *
     * @param string $title Partner title.
     *
     * @return  Partner                     Partner.
     */
    protected function buildNewPartner(string $title): Partner
    {
        $partner = new Partner();
        $partnerTitle = trim($title);
        $this->setItemLocaleChild($partner, new PartnerTranslation(), $this->locale);

        $partner->translate($this->locale)->setName($this->mb_ucfirst($partnerTitle));

        try {
            $file = $this->fileManager->copyEntityFile(
                $this->defaultImage,
                $this->partnersImagesDirectory
            );
            $partner->setImage($file->getFilename());
        } catch (RuntimeException $exception) {

        }

        return $partner;
    }

    /**
     * Add webinar expert.
     *
     * @param Item $entity Webinar.
     * @param string $expertTitle Expert title.
     * @param string[] $positions Expert positions.
     *
     * @return  void
     */
    protected function addExpert(
        Item $entity,
        string $expertTitle,
        array $positions = []
    ): void {
        if (strlen($expertTitle) === 0) {
            return;
        }

        if (isset($this->experts[$this->mb_ucfirst(trim($expertTitle))])) {
            $entity->addExpert($this->experts[$this->mb_ucfirst(trim($expertTitle))]);
        } else {
            $expert = $this->buildNewExpert(trim($expertTitle), $positions);
            $this->experts[$this->mb_ucfirst(trim($expertTitle))] = $expert;
            $entity->addExpert($expert);
            $this->entityManager->persist($expert);
        }
    }

    /**
     * Build new expert.
     *
     * @param string $title Expert title.
     * @param string[] $positions Expert positions.
     *
     * @return  Expert                      Expert.
     */
    protected function buildNewExpert(string $title, array $positions = []): Expert
    {
        $expertTitle = $this->mb_ucfirst(trim($title));
        $expert = new Expert();
        $this->setItemLocaleChild($expert, new ExpertTranslation(), $this->locale);

        $expert
            ->translate($this->locale)
            ->setName($expertTitle);
        $expert
            ->translate($this->locale)
            ->setContent(implode(', ', $positions));

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
                        $expertType = $this->buildNewExpertType($position);
                        $this->expertsTypes[ucfirst($position)] = $expertType;
                        $expert->addExpertType($expertType);
                        $this->entityManager->persist($expertType);
                    }
                }
            }
        }

        return $expert;
    }

    /**
     * Build new expert type.
     *
     * @param string $title Title.
     *
     * @return  ExpertType                  Expert type.
     */
    protected function buildNewExpertType(string $title): ExpertType
    {
        $expertType = new ExpertType();
        $this->setItemLocaleChild($expertType, new ExpertTypeTranslation(), $this->locale);
        $expertType->translate($this->locale)->setName($this->mb_ucfirst($title));

        return $expertType;
    }

    /**
     * Set entity category.
     *
     * @param Item $entity Entity.
     * @param string $categoryTitle Category title.
     *
     * @return  void
     */
    protected function setCategory(Item $entity, string $categoryTitle): void
    {
        if (strlen($categoryTitle) === 0) {
            $entity->setCategory(null);
            return;
        }

        if ($categoryTitle === 'Бізнес онлайн') {
            $categoryTitle = 'Бізнес он-лайн';
        }

        if (isset($this->categories[$this->mb_ucfirst($categoryTitle)])) {
            $entity->setCategory($this->categories[$this->mb_ucfirst($categoryTitle)]);
        } else {
            $category = $this->buildNewCategory($categoryTitle);
            $this->categories[$this->mb_ucfirst($categoryTitle)] = $category;
            $entity->setCategory($category);
            $this->entityManager->persist($category);
        }
    }

    /**
     * Build new category.
     *
     * @param string $title Title.
     *
     * @return  Category                    Category.
     */
    protected function buildNewCategory(string $title): Category
    {
        $category = new Category();
        $this->setItemLocaleChild($category, new CategoryTranslation(), $this->locale);

        $category->setActive(true);
        $category->setSort(500);
        $category->setSlug($this->slugify->slugify($title));
        $category->translate($this->locale)->setName(ucfirst($title));

        try {
            $file = $this->fileManager->copyEntityFile(
                $this->defaultImage,
                $this->categoriesImagesDirectory
            );
            $category->setImage($file->getFilename());
        } catch (RuntimeException $exception) {

        }

        return $category;
    }

    /**
     * Add webinar crop.
     *
     * @param Item $entity Item.
     * @param string $cropTitle Crop title.
     *
     * @return  void
     */
    protected function addCrop(Item $entity, string $cropTitle): void
    {
        if (strlen($cropTitle) === 0) {
            return;
        }

        if (isset($this->crops[$this->mb_ucfirst($cropTitle)])) {
            $entity->addCrop($this->crops[$this->mb_ucfirst($cropTitle)]);
        } else {
            $crop = $this->buildNewCrop($cropTitle);
            $this->crops[$this->mb_ucfirst($cropTitle)] = $crop;
            $entity->addCrop($crop);
            $this->entityManager->persist($crop);
        }
    }

    /**
     * Build new crop.
     *
     * @param string $title Title.
     *
     * @return  Crop                        Crop.
     */
    protected function buildNewCrop(string $title): Crop
    {
        $crop = new Crop();
        $this->setItemLocaleChild($crop, new CropTranslation(), $this->locale);

        $crop->translate($this->locale)->setName($this->mb_ucfirst($title));
        $crop->translate($this->locale)->setContent($title);

        try {
            $file = $this->fileManager->copyEntityFile(
                $this->defaultImage,
                $this->cropsImagesDirectory
            );
            $crop->setImageName($file->getFilename());
        } catch (RuntimeException $exception) {

        }

        return $crop;
    }

    /**
     * Add webinar tag.
     *
     * @param Webinar $webinar Webinar.
     * @param string $tagTitle Tag title.
     *
     * @return  void
     */
    protected function addWebinarTag(Webinar $webinar, string $tagTitle): void
    {
        if (strlen($tagTitle) === 0) {
            return;
        }

        if (isset($this->tags[$this->mb_ucfirst($tagTitle)])) {
            $webinar->addTag($this->tags[$this->mb_ucfirst($tagTitle)]);
        } else {
            $tag = $this->buildNewTag($tagTitle);
            $this->tags[$this->mb_ucfirst($tagTitle)] = $tag;
            $webinar->addTag($tag);
            $this->entityManager->persist($tag);
        }
    }

    /**
     * Build new tag.
     *
     * @param string $title Title.
     *
     * @return  Tags                        Tag.
     */
    protected function buildNewTag(string $title): Tags
    {
        $tag = new Tags();
        $this->setItemLocaleChild($tag, new TagsTranslation(), $this->locale);
        $tag->translate($this->locale)->setName($this->mb_ucfirst($title));

        $slug = $this->slugify->slugify($tag->getTranslations()['uk']->getName());
        $tag->setSlug($slug);
        $this->tags[$this->mb_ucfirst($title)] = $tag;
        return $tag;
    }

    /**
     * Set webinar video.
     *
     * @param $entity Webinar.
     * @param string $videoId Video ID.
     *
     * @return  void
     */
    protected function setVideo($entity, string $videoId): void
    {
        if (strlen($videoId) === 0) {
            $entity->setVideoItem(null);
            return;
        }

        if (isset($this->videos[$videoId])) {
            $videoItem = $this->videos[$videoId];
        } else {
            $videoItem = $this->buildNewVideoItem($videoId);
            $this->videos[$videoId] = $videoItem;
            $this->entityManager->persist($videoItem);
        }

        try {
            $videoDuration = $this->calculateVideoDuration($videoId);
            $videoItem->setVideoDuration($videoDuration);
        } catch (RuntimeException $exception) {
            $videoItem->setVideoDuration(0);
        }

        $entity->setVideoItem($videoItem);
    }

    /**
     * Build new video item.
     *
     * @param string $videoId Video ID.
     *
     * @return  VideoItem                   Video item.
     */
    protected function buildNewVideoItem(string $videoId): VideoItem
    {
        $video = new VideoItem();
        $this->setItemLocaleChild($video, new VideoItemTranslation(), $this->locale);

        $video->translate($this->locale)->setTitle("Video $videoId");
        $video->setVideoId($videoId);

        return $video;
    }

    /**
     * Try to calculate video duration, using it`s ID.
     *
     * @param   string $videoId             Video ID.
     *
     * @return  int                         Video duration (in seconds).
     * @throws  RuntimeException            Process failed.
     */
    protected function calculateVideoDuration(string $videoId): int
    {
        if (strlen($videoId) === 0) {
            throw new RuntimeException('video ID is empty');
        }

        try {
            $videoData              = $this
                ->youtubeVideoDataReader
                ->read($videoId);
            $videoDurationString    = (string) ($videoData['items'][0]['contentDetails']['duration'] ?? '');
        } catch (InvalidArgumentException | RuntimeException $exception) {
            throw new RuntimeException(
                'video data receiving failed',
                0,
                $exception
            );
        }

        if (strlen($videoDurationString) === 0) {
            throw new RuntimeException('empty video duration received');
        }

        try {
            $time = new DateInterval($videoDurationString);
            return ($time->h * 60 * 60) + ($time->i * 60) + $time->s;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "convert $videoDurationString to seconds failed",
                0,
                $exception
            );
        }
    }

    /**
     * Build new article.
     *
     * @return  Article                     Article.
     */
    protected function buildNewArticle(): Article
    {
        $article = new Article();
        $this->setItemLocaleChild($article, new ArticleTranslation(), $this->locale);
        $article->setIsActive(true);

        return $article;
    }

    /**
     * Find exist file, or fire error if not exist.
     *
     * @param string $filePath File path.
     *
     * @return  SplFileInfo                 File.
     * @throws  RuntimeException            File was not found.
     */
    protected function findExistFile(string $filePath): SplFileInfo
    {
        $file = new SplFileInfo($filePath);

        if (!$file->isFile()) {
            throw new RuntimeException("{$file->getPathname()} is not a file");
        }

        return $file;
    }

    /**
     * Find exist writable directory, or fire error if not exist.
     *
     * @param string $directoryPath Directory path.
     *
     * @return  SplFileInfo                 Directory.
     * @throws  RuntimeException            Directory was not found.
     */
    protected function findExistWritableDirectory(string $directoryPath): SplFileInfo
    {
        $directory = new SplFileInfo($directoryPath);
        if (!$directory->isDir()) {
            $result = @mkdir($directory->getPathname(), 0777, true);

            if (!$result) {
                throw new RuntimeException("could not create directory{$directory->getPathname()}");
            }
        }
        if (!$directory->isWritable()) {
            throw new RuntimeException("directory {$directory->getPathname()} is not a writable");
        }

        return $directory;
    }

    /**
     * Copy file to directory and get it`s copy.
     *
     * @param SplFileInfo $file File.
     * @param SplFileInfo $directory Directory.
     *
     * @return  SplFileInfo                 File copy.
     * @throws  RuntimeException            Process failed.
     */
    protected function copyFileToDirectory(SplFileInfo $file, SplFileInfo $directory): SplFileInfo
    {
        $this->findExistFile($file);

        $currentTimestamp = (new DateTime('now'))->getTimestamp();
        $fileHash = hash_file('md5', $file->getPathname());

        while (true) {
            $newFile = new SplFileInfo(
                $directory->getPathname() . DIRECTORY_SEPARATOR .
                "$currentTimestamp-$fileHash.{$file->getExtension()}"
            );

            if ($newFile->isFile()) {
                $currentTimestamp += 1;
            } else {
                break;
            }
        }

        $copyResult = copy($file->getPathname(), $newFile->getPathname());
        if (!$copyResult) {
            throw new RuntimeException(
                "failed to copy file from {$file->getPathname()} " .
                "to {$newFile->getPathname()}"
            );
        }
        return $newFile;
    }

    /**
     * Set item locale child.
     *
     * @param TranslatableInterface $item Item.
     * @param TranslationInterface $translation Item translation.
     * @param string $locale Locale.
     *
     * @return  void
     */
    protected function setItemLocaleChild(
        TranslatableInterface $item,
        TranslationInterface $translation,
        string $locale
    ): void {
        $translation->setLocale($locale);
        $item->addTranslation($translation);
        $translation->setTranslatable($item);
    }
}
