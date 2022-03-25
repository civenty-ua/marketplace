<?php
declare(strict_types = 1);

namespace App\Controller\Admin\Market;

use Throwable;
use EasyCorp\Bundle\EasyAdminBundle\{
    Config\Action,
    Config\Actions,
    Config\Crud,
    Config\Filters,
    Config\KeyValueStore,
    Collection\FieldCollection,
    Factory\FilterFactory,
    Field\AssociationField,
    Field\BooleanField,
    Field\DateTimeField,
    Field\IdField,
    Field\IntegerField,
    Field\TextField,
    Field\TextareaField,
};
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Service\ExportService;
use App\Repository\{
    UserRepository,
    Market\CommodityRepository,
};
use App\Entity\{
    User,
    Market\Commodity,
    Market\CommodityProduct,
    Market\CommodityService,
    Market\CommodityKit,
};
/**
 * KitCrudController.
 */
class CommodityKitCrudController extends CommodityCrudController
{
    /**
     * @inheritdoc
     */
    public static function getEntityFqcn(): string
    {
        return CommodityKit::class;
    }
    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->setLabel('ID')
            ->onlyOnIndex();
        yield TextField::new('title')
            ->setLabel('admin.market.product.title')
            ->setRequired(true)
            ->setColumns('col-sm-6 col-md-4');
        yield IntegerField::new('price')
            ->setLabel('admin.market.product.price')
            ->setColumns('col-sm-6 col-md-4')
            ->setRequired(true);
        yield TextareaField::new('description')
            ->setLabel('admin.market.product.description')
            ->setColumns('col-sm-12 col-md-12')
            ->setFormType(CKEditorType::class);
        yield AssociationField::new('commodities')
            ->setLabel('admin.market.kit.commodities')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms()
            ->setFormTypeOptions([
                'query_builder'     => function(CommodityRepository $repository) {
                    $query = $repository->createQueryBuilder('c');
                    $query
                        ->andWhere($query->expr()->orX(
                            $query->expr()->isInstanceOf('c',CommodityProduct::class),
                            $query->expr()->isInstanceOf('c',CommodityService::class)
                        ))
                        ->orderBy('c.title', 'ASC');

                    return $query;
                },
                'choice_label'  => function(?Commodity $commodity) {
                    return $commodity
                        ? "{$commodity->getId()}: {$commodity->getTitle()} (User: {$commodity->getUser()->getEmail()})"
                        : '';
                },
                'choice_attr'   => function(?Commodity $commodity) {
                    if (!$commodity) {
                        return [];
                    }

                    try {
                        $this->commodityActivityManager->checkCommodityIsPublished($commodity);
                        $commodityIsActive = true;
                    } catch (Throwable $exception) {
                        $commodityIsActive = false;
                    }

                    return [
                        'data-activity' => $commodityIsActive ? 'Y' : 'N',
                    ];
                },
            ])
            ->setRequired(true);
        yield AssociationField::new('user')
            ->setLabel('admin.market.product.user')
            ->setColumns('col-sm-6 col-md-4')
            ->setRequired(true)
            ->setFormTypeOptions([
                'query_builder' => function (UserRepository $repository) {
                    $alias          = 'user';
                    $queryBuilder   = $repository->createQueryBuilder($alias);
                    $roles          = [
                        User::ROLE_SALESMAN,
                        User::ROLE_SERVICE_PROVIDER,
                    ];

                    foreach ($roles as $index => $role) {
                        $parameterName = "role_$index";
                        $queryBuilder
                            ->orWhere("$alias.roles LIKE :$parameterName")
                            ->setParameter($parameterName, "%\"$role\"%");
                    }
                    $queryBuilder->andWhere("$alias.isBanned = false")
                    ->orderBy('user.name', 'ASC');

                    return $queryBuilder;
                },
                'attr'          => [
                    'data-commodity-user-field' => 'Y',
                ],
            ]);
        yield BooleanField::new('isActive')
            ->setLabel('IsActive')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnIndex()
            ->setFormTypeOption('disabled','disabled');
        yield BooleanField::new('isActive')
            ->setLabel('admin.market.product.activity')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield BooleanField::new('isApproved')
            ->setLabel('admin.market.kit.approved')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield DateTimeField::new('activeFrom')
            ->setLabel('admin.market.product.active_from')
            ->setColumns('col-sm-6 col-md-4');
        yield DateTimeField::new('activeTo')
            ->setLabel('admin.market.product.active_to')
            ->setColumns('col-sm-6 col-md-4');

        yield TextField::new('metaTitle')
            ->setLabel('Метатег title')
            ->setColumns('col-md-12')
            ->onlyOnForms();

        yield TextField::new('metaKeywords')
            ->setLabel('Метатег keywords')
            ->setColumns('col-md-12')
            ->onlyOnForms();

        yield TextareaField::new('metaDescription')
            ->setLabel('Метатег description')
            ->setColumns('col-md-12')
            ->onlyOnForms();
    }
    /**
     * @inheritdoc
     */
    protected function getMessagesDomain(): string
    {
        return 'kit';
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('isActive')
            ->add('title')
            ->add('price')
            ->add('user')
            ->add('activeFrom')
            ->add('activeTo');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('kitExport', 'Експорт')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('kitExport')
                    ->addCssClass('btn btn-primary')
            );
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function kitExport(ExportService $exportService): void
    {
        $context = $this->getContext();
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filters = $this->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $query = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);
        $query->setMaxResults(1000);
        $productsList = $query->getQuery()->getResult();
        $exportData = [];

        /** @var CommodityKit $product */
        foreach ($productsList as $product) {
            $exportData[] = [
                'ID' => $product->getId(),
                'Назва' => $product->getTitle(),
                'Ціна' => $product->getPrice() == 0 ? 'Договірна' : $product->getPrice() ,
                'Опис' => $product->getDescription(),
                'Користувач' => $product->getUser()->getName(),
                'Телефони користувач' => $product->getUser()->getPhone(), //TODO телефоны поменять
                'Активний' => $product->getIsActive() ? 'Так' : 'Ні',
                'Активний з' => $product->getActiveFrom(),
                'Активний по' => $product->getActiveTo()
            ];
        }

        $exportService->export('commodityKit', $exportData);
    }
    /**
     * @inheritDoc
     */
    protected function updateFormOptions(KeyValueStore $formOptions): void
    {
        parent::updateFormOptions($formOptions);
        $this->addFormParameters($formOptions, [
            'data-commodity-kit-form' => 'Y',
        ]);
    }
}
