<?php

namespace App\Controller\Admin;

use App\Admin\Field\TranslationField;
use App\Entity\LessonModule;
use App\Entity\LessonSort;
use App\Repository\LessonRepository;
use App\Service\ExportService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Provider\AdminContextProvider;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Filesystem\Filesystem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class LessonModuleCrudController extends BaseCrudController
{
    private ?string $currentLocale;

    public function __construct(RequestStack  $requestStack)
    {
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }

    public static function getEntityFqcn(): string
    {
        return LessonModule::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fieldsConfig = [
            'title' => [
                'field_type' => TextType::class,
                'required' => true,
                'label' => 'Заголовок',
                'row_attr' => [
                    'class' => 'col-md-4',
                ],
            ],
            'description' => [
                'field_type' => TextType::class,
                'required' => false,
                'label' => 'Опис',
                'row_attr' => [
                    'class' => 'col-md-8',
                ],
            ],
            'content' => [
                'field_type' => CKEditorType::class,
                'required' => false,
                'label' => 'Текст',
            ],

        ];

        $kernelDir = $this->get('parameter_bag')->get('kernel.project_dir');
        $dir = $kernelDir . '/public/upload/lessonModule';
        $fs = new Filesystem();
        $fs->mkdir($dir);

        //Form
//        $img = ImageField::new('image')
//            ->setUploadDir('public/upload/lessonModule')
//            ->setUploadedFileNamePattern('[timestamp]-[contenthash].[extension]')
//            ->setBasePath('upload/lessonModule')
//            ->onlyOnForms()
//            ->setColumns('col-sm-6 col-md-4')
//            ->setLabel('Зображення');
//
//        if (Crud::PAGE_NEW === $pageName) {
//            yield $img->setRequired(true);
//        } else {
//            yield $img->setRequired(false);
//        }

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

        yield DateTimeField::new('startDate')
            ->setLabel('Час початку')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();


            yield AssociationField::new('lessons')
                ->setLabel('Уроки')
                ->setColumns('col-sm-6 col-md-4')
                ->setFormTypeOptions([
                    'by_reference' => false,
                    'query_builder' => function (LessonRepository $lesson) {
                        return $lesson->createQueryBuilder('l')
                            ->leftJoin('l.lessonSorts', 'ls')
                            ->andWhere('l.active = :active')
                            //->andWhere('ls.course = ' . $lessonModule->getId())
                            ->orderBy('ls.sort', 'ASC')
                            ->setParameter('active', true);
                    },
                ])->onlyOnForms();


        yield TranslationField::new('translations', 'Переклади', $fieldsConfig)
            ->setRequired(true)
            ->hideOnIndex();


        //Index
        yield IdField::new('id')->onlyOnIndex();
        yield TextField::new('title')->onlyOnIndex()->setLabel('Заголовок');

//        yield ImageField::new('image')
//            ->setBasePath('upload/lessonModule')
//            ->onlyOnIndex()
//            ->setLabel('Зображення');

        yield DateTimeField::new('startDate')->setLabel('Час початку')->hideOnIndex();
        yield AssociationField::new('lessons')
            ->setLabel('Уроки')
            ->setFormTypeOptions([
                'by_reference' => false,
            ])->hideOnForm();

    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('lessons');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['translations.title'])
            ->setEntityLabelInPlural('admin.dashboard.course.module.list_title')
            ->setEntityLabelInSingular('admin.dashboard.course.module.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.course.module.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.course.module.edit_page_title')
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
                Action::new('lessonModuleExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('lessonModuleExport')
                    ->addCssClass('btn btn-primary'))
            ->update(Crud::PAGE_INDEX,
                Action::DELETE,
                function (Action $action) {
                    $dto = $action->getAsDto();
                    $dto->setDisplayCallable(function ($entity) {
                        return $entity->getLessons()->toArray() === [];
                    });

                    return $action;
                })
            ->update(Crud::PAGE_INDEX,
                Action::BATCH_DELETE,
                function (Action $action) {
                    $dto = $action->getAsDto();
                    $dto->setDisplayCallable(function ($entity) {
                        return $entity->getLessons()->toArray() === [];
                    });

                    return $action;
                });
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function lessonModuleExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:LessonModule')
            ->createQueryBuilder('lm')
            ->setMaxResults(1000)
            ->getQuery();

        $lessonModuleList = $query->getResult();

        $exportData = [];

        /** @var LessonModule $lessonModule */
        foreach ($lessonModuleList as $lessonModule) {

            $lessonList = [];
            foreach ($lessonModule->getLessons() as $lesson) {
                $lessonList[] = $lesson->getTitle();
            }
            $exportData[] = [
                'ID' => $lessonModule->getId(),
                'Уроки' => implode(', ', $lessonList),
                'Назва' => $lessonModule->getTitle(),
                'Текст' => $lessonModule->getContent(),
                'Експерт' => !is_null($lessonModule->getExpert()) ? $lessonModule->getExpert()->getName() : '',
            ];
        }

        $exportService->export('lessonModule', $exportData);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $this->saveEntityAndSort($entityManager, $entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void {
        $this->saveEntityAndSort($entityManager, $entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    private function saveEntityAndSort(EntityManagerInterface $entityManager, &$entityInstance) {
        $oldLessonSort = $entityManager->getRepository(LessonSort::class)->findBy(['lessonModule' => $entityInstance]);
        foreach ($oldLessonSort as $item) {
            $entityManager->remove($item);
        }
        $sortCounter = 0;
        $lessonSort = [];
        $lessons = $entityInstance->getLessonsSort();
        foreach ($lessons as $entity) {
            $lessonSort[$sortCounter] = new LessonSort();
            $lessonSort[$sortCounter]->setLessonModule($entityInstance);
            $lessonSort[$sortCounter]->setLesson($entity);
            $lessonSort[$sortCounter]->setSort($sortCounter);
            $entityManager->persist($lessonSort[$sortCounter]);
            $sortCounter++;
        }
    }
}
