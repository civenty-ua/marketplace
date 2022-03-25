<?php
declare(strict_types = 1);

namespace App\Controller\Admin\Market;

use App\Service\ExportService;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    BooleanField,
    DateTimeField,
    IdField,
    IntegerField,
    TextField,
    TextareaField,
};
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Factory\FilterFactory;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Admin\Field\Market\CategoryField;
use App\Repository\UserRepository;
use App\Admin\Field\{
    VichImageField,
    Market\CommodityAttributesField,
};
use App\Entity\{
    User,
    Market\Category,
    Market\Commodity,
    Market\CommodityService,
};
/**
 * ServiceCrudController.
 */
class CommodityServiceCrudController extends CommodityCrudController
{
    /**
     * @inheritdoc
     */
    public static function getEntityFqcn(): string
    {
        return CommodityService::class;
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
        yield VichImageField::new('imageFile')
            ->setColumns('col-sm-6 col-md-4 hide-delete-btn')
            ->setLabel('admin.market.product.image')
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->onlyOnForms();
        yield TextareaField::new('description')
            ->setLabel('admin.market.product.description')
            ->setColumns('col-sm-12 col-md-12')
            ->setRequired(true)
            ->setFormType(CKEditorType::class);
        yield CategoryField::new(
                'category',
                'admin.market.product.category',
                Commodity::TYPE_SERVICE,
                $this->getDoctrine()
            )
            ->setColumns('col-sm-6 col-md-4')
            ->setRequired(true);
        yield AssociationField::new('user')
            ->setLabel('admin.market.product.user')
            ->setColumns('col-sm-6 col-md-4')
            ->setRequired(true)
            ->setFormTypeOptions([
                'query_builder' => function (UserRepository $repository) {
                    $alias  = 'user';
                    $role   = User::ROLE_SERVICE_PROVIDER;

                    return $repository->createQueryBuilder($alias)
                        ->andWhere("$alias.isBanned = false")
                        ->andWhere("$alias.roles LIKE :role")
                        ->setParameter('role', "%\"$role\"%")
                        ->orderBy('user.name', 'ASC');
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
        yield DateTimeField::new('activeFrom')
            ->setLabel('admin.market.product.active_from')
            ->setColumns('col-sm-6 col-md-4');
        yield DateTimeField::new('activeTo')
            ->setLabel('admin.market.product.active_to')
            ->setColumns('col-sm-6 col-md-4');
        yield CommodityAttributesField::new('commodityAttributesValues')
            ->setLabel('admin.market.product.attributes_values')
            ->setColumns('col-md-8 col-xxl-7')
            ->onlyOnForms();

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
        return 'service';
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('isActive')
            ->add('title')
            ->add('price')
            ->add('category')
            ->add('user')
            ->add('activeFrom')
            ->add('activeTo');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('serviceExport', 'Експорт')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('serviceExport')
                    ->addCssClass('btn btn-primary')
            );
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function serviceExport(ExportService $exportService): void
    {
        $context = $this->getContext();
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filters = $this->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $query = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);
        $query->setMaxResults(1000);
        $productsList = $query->getQuery()->getResult();
        $exportData = [];

        /** @var CommodityService $product */
        foreach ($productsList as $product) {
            $exportData[] = [
                'ID' => $product->getId(),
                'Назва' => $product->getTitle(),
                'Ціна' => $product->getPrice() == 0 ? 'Договірна' : $product->getPrice() ,
                'Опис' => $product->getDescription(),
                'Категорія' => !is_null($product->getCategory()) ? $product->getCategory()->getTitle() : '',
                'Користувач' => $product->getUser()->getName(),
                'Телефони користувач' => $product->getUser()->getPhone(), //TODO телефоны поменять
                'Активний' => $product->getIsActive() ? 'Так' : 'Ні',
                'Активний з' => $product->getActiveFrom(),
                'Активний по' => $product->getActiveTo()
            ];
        }

        $exportService->export('commodityService', $exportData);
    }
}
