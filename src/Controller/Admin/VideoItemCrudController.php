<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use Throwable;
use RuntimeException;
use InvalidArgumentException;
use DateInterval;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use App\Service\YoutubeClient;
use App\Entity\VideoItem;
use App\Admin\Field\TranslationField;
use function strlen;

class VideoItemCrudController extends BaseCrudController
{
    private $youtubeVideoDataReader;

    public function __construct(YoutubeClient $youtubeVideoDataReader)
    {
        $this->youtubeVideoDataReader = $youtubeVideoDataReader;
    }

    public static function getEntityFqcn(): string
    {
        return VideoItem::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->onlyOnIndex();
        yield TextField::new('title')
            ->onlyOnIndex()
            ->setLabel('Назва');
        yield DateField::new('createdAt')
            ->hideOnForm()
            ->setLabel('Дата створення');
        yield TextField::new('videoLink')
            ->onlyOnForms()
            ->setLabel('Відео')
            ->setRequired(true);
        yield TextField::new('videoLink')
            ->onlyOnIndex()
            ->setTemplatePath('admin/video/youtube.html.twig')
            ->setLabel('Відео')
            ->setFormTypeOptions([
                'block_name' => 'youtube_link',
            ]);
        yield IntegerField::new('videoDuration')
            ->setLabel('Довжина')->onlyOnIndex();
        yield TranslationField::new('translations',
            'Переклади',
            [
                'title' => [
                    'field_type' => TextType::class,
                    'required' => true,
                    'label' => 'Назва',
                ],
                'description' => [
                    'field_type' => TextType::class,
                    'required' => false,
                    'label' => 'Метатег description',
                ],
                'keywords' => [
                    'field_type' => TextType::class,
                    'required' => false,
                    'label' => 'Метатег keywords',
                ],

            ])
            ->setRequired(true)
            ->hideOnIndex();
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['id', 'translations.title', 'createdAt'])
            ->setEntityLabelInPlural('admin.dashboard.video.list_title')
            ->setEntityLabelInSingular('admin.dashboard.video.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.video.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.video.edit_page_title')
            ->setFormOptions(
                ['validation_groups' => ['Default', 'creation']],
                ['validation_groups' => ['Default', 'creation']]
            )
            ->setFormThemes([
                '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
            ])
            ->showEntityActionsInlined();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $videoItem): void
    {
        $this->prepareVideoBeforeSaving($videoItem);
        parent::persistEntity($entityManager, $videoItem);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $videoItem): void
    {
        $this->prepareVideoBeforeSaving($videoItem);
        parent::updateEntity($entityManager, $videoItem);
    }

    private function prepareVideoBeforeSaving(VideoItem $videoItem): void
    {
        try {
            $videoData = $this
                ->youtubeVideoDataReader
                ->read($videoItem->getVideoId() ?? '');
            $videoDurationString = (string)($videoData['items'][0]['contentDetails']['duration'] ?? '');
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
            $videoDurationSeconds = ($time->h * 60 * 60) + ($time->i * 60) + $time->s;
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "convert $videoDurationString to seconds failed",
                0,
                $exception
            );
        }

        $videoItem->setVideoDuration($videoDurationSeconds);
    }
}
