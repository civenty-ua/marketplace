<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\Lesson;
use App\Service\ExportService;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\{Request, RequestStack};

class LessonCrudController extends BaseCrudController
{
    private ?string $currentLocale;

    public function __construct(RequestStack  $requestStack)
    {
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return Lesson::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'title' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => 'Заголовок',
                'row_attr' => [
                    'class' => 'col-md-6',
                ],
                'block_name' => 'title',
                'block_prefix' => 'translate',
            ],
            'metaTitle' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Метатег title',
                'row_attr' => [
                    'class' => 'col-md-6',
                ],
            ],
            'keywords' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Метатег кeywords',
                'row_attr' => [
                    'class' => 'col-md-6',
                ],
            ],
            'description' => [
                'field_type' => TextareaType::class,
                'required' => false,
                'label' => 'Метатег description',
                'row_attr' => [
                    'class' => 'col-md-6 item-meta-description',
                ],
                'attr' => [
                    'class' => 'item-meta-description',
                ],
            ],
            'content' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Текст',
                'row_attr' => [
                    'class' => 'col-md-12 float',
                ],
            ],
        ];

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $dir = $kernelDir . '/public/upload/lesson';
        $fs = new Filesystem();
        $fs->mkdir($dir);


        //Form
//        $img = ImageField::new('imageName')
//            ->setUploadDir('public/upload/lesson')
//            ->setColumns('col-sm-6 col-md-4')
//            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
//            ->setBasePath('upload/lesson')
//            ->onlyOnForms()
//            ->setLabel('Зображення');
//
//        if (Crud::PAGE_NEW === $pageName) {
//            yield $img->setRequired(true);
//        } else {
//            yield $img->setRequired(false);
//        }

        yield AssociationField::new('videoItem')
            ->onlyOnForms()->setLabel('Відео')
            ->setColumns('col-sm-6 col-md-4');
        yield BooleanField::new('active')
            ->onlyOnForms()
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('Активна');

        yield AssociationField::new('expert')
            ->onlyOnForms()
            ->setLabel('Експерт')
            ->setColumns('col-sm-6 col-md-4')
            ->setFormTypeOptions([
                'by_reference' => true,
                'query_builder' => function(EntityRepository $repository) {
                    return $repository
                        ->createQueryBuilder('experts')
                        ->leftJoin('experts.translations', 'expertsTranslations')
                        ->where('expertsTranslations.locale = :locale')
                        ->setParameter('locale',  $this->currentLocale)
                        ->orderBy('expertsTranslations.name', 'ASC');
                },
            ])->hideOnIndex();

        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();

        //Index
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('title')->onlyOnIndex()->setLabel('Назва');
        yield BooleanField::new('active')
            ->onlyOnIndex()
            ->setLabel('Активна');

//        yield ImageField::new('imageName')
//            ->setBasePath('upload/lesson')
//            ->onlyOnIndex()
//            ->setLabel('Зображення');

        yield DateField::new('createdAt')
            ->onlyOnIndex()->setLabel('Дата створення');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('active')
            ->add('createdAt')
            ->add('updatedAt');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['translations.title'])
            ->setEntityLabelInPlural('admin.dashboard.course.lesson.list_title')
            ->setEntityLabelInSingular('admin.dashboard.course.lesson.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.course.lesson.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.course.lesson.edit_page_title')
            ->setFormOptions(['validation_groups' => ['Default', 'creation']],
                ['validation_groups' => ['Default', 'creation']])
            ->setFormThemes(
                [
                    '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                    '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                    '@EasyAdmin/crud/form_theme.html.twig',
                ]
            )
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(Crud::PAGE_INDEX,
                Action::new('lessonExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('lessonExport')
                    ->addCssClass('btn btn-primary'));
    }

    /**
     * @param ExportService $exportService
     * @param Request $request
     *
     * @return void
     */
    public function lessonExport(ExportService $exportService, Request $request): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:Lesson')
            ->createQueryBuilder('l')
            ->setMaxResults(1000)
            ->getQuery();

        $lessonList = $query->getResult();

        $exportData = [];

        /** @var Lesson $lesson */
        foreach ($lessonList as $lesson) {

            $exportData[] = [
                'ID' => $lesson->getId(),
                'Назва' => $lesson->getTitle(),
                'Текст' => $lesson->getContent(),
                'Метатег title' => $lesson->getMetaTitle(),
                'Метатег кeywords' => $lesson->getKeywords(),
                'Метатег description' => $lesson->getDescription(),
                'Дата створення' => $lesson->getCreatedAt(),
                'Дата редагування' => $lesson->getUpdatedAt(),
            ];
        }

        $exportService->export('lesson', $exportData);
    }
}
