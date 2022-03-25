<?php
declare(strict_types = 1);

namespace App\Controller\Admin\Market;

use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\Form\FormBuilderInterface;
use EasyCorp\Bundle\EasyAdminBundle\{
    Collection\FieldCollection,
    Config\Action,
    Config\Actions,
    Config\Filters,
    Config\Crud,
    Config\KeyValueStore,
    Context\AdminContext,
    Dto\EntityDto,
    Factory\FilterFactory,
    Router\AdminUrlGenerator,
};
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    BooleanField,
    ChoiceField,
    DateTimeField,
    IdField,
    IntegerField,
    TextField,
    TextareaField,
};
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use App\Service\ExportService;
use App\Admin\Field\Market\CategoryField;
use App\Repository\UserRepository;
use App\Traits\LocationTrait;
use App\Admin\Field\{
    VichImageField,
    Market\CommodityAttributesField,
};
use App\Entity\{
    User,
    Market\Commodity,
    Market\CommodityProduct,
};
/**
 * ProductCrudController.
 */
class CommodityProductCrudController extends CommodityCrudController
{
    use LocationTrait;
    /**
     * @inheritdoc
     */
    public static function getEntityFqcn(): string
    {
        return CommodityProduct::class;
    }
    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        $typesChoices = [];
        foreach (CommodityProduct::getAvailableTypes() as $type) {
            $typeTitle = $this->translator->trans("admin.market.product.types.$type");
            $typesChoices[$typeTitle] = $type;
        }

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
        yield BooleanField::new('isActive')
            ->setLabel('admin.market.product.activity')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnForms();
        yield CategoryField::new(
                'category',
                'admin.market.product.category',
                Commodity::TYPE_PRODUCT,
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
                    $role   = User::ROLE_SALESMAN;

                    return $repository->createQueryBuilder($alias)
                        ->andWhere("$alias.isBanned = false")
                        ->andWhere("$alias.roles LIKE :role")
                        ->setParameter('role', "%\"$role\"%")
                        ->orderBy('user.name', 'ASC');
                },
            ]);
        yield ChoiceField::new('type')
            ->setLabel('admin.market.product.type')
            ->setChoices($typesChoices)
            ->setColumns('col-sm-6 col-md-4')
            ->setRequired(true);
        yield BooleanField::new('isOrganic')
            ->setLabel('admin.market.product.isOrganic')
            ->setColumns('col-sm-6 col-md-4');
        yield BooleanField::new('isActive')
            ->setLabel('IsActive')
            ->setColumns('col-sm-6 col-md-4')
            ->onlyOnIndex()
            ->setFormTypeOption('disabled',true);
        yield CommodityAttributesField::new('commodityAttributesValues')
            ->setLabel('admin.market.product.attributes_values')
            ->setColumns('col-md-8 col-xxl-7')
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
        return 'product';
    }

    /**
     * @param EntityDto $entityDto
     * @param KeyValueStore $formOptions
     * @param AdminContext $context
     * @return FormBuilderInterface
     */
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        return $this->addedLocationUserField($formBuilder);
    }

    /**
     * @param EntityDto $entityDto
     * @param KeyValueStore $formOptions
     * @param AdminContext $context
     * @return FormBuilderInterface
     */

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        return $this->addedLocationUserField($formBuilder);
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
            ->add('isOrganic')
            ->add('activeFrom')
            ->add('activeTo');
    }

    public function configureActions(Actions $actions): Actions
    {
        $getProductAllowedTypesAction = Action::new('getProductAllowedTypes')
            ->linkToCrudAction('getProductAllowedTypes')
            ->setHtmlAttributes([
                'style' => 'display: none;',
            ]);

        return parent::configureActions($actions)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('productExport', 'Експорт')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('productExport')
                    ->addCssClass('btn btn-primary')
            )
            ->add(Crud::PAGE_NEW, $getProductAllowedTypesAction)
            ->add(Crud::PAGE_EDIT, $getProductAllowedTypesAction);
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function productExport(ExportService $exportService): void
    {
        $context = $this->getContext();
        $fields = FieldCollection::new($this->configureFields(Crud::PAGE_INDEX));
        $filters = $this->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        $query = $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);
        $query->setMaxResults(1000);
        $productsList = $query->getQuery()->getResult();
        $exportData = [];

        /** @var CommodityProduct $product */
        foreach ($productsList as $product) {
            $exportData[] = [
                'ID' => $product->getId(),
                'Назва' => $product->getTitle(),
                'Ціна' => $product->getPrice() == 0 ? 'Договірна' : $product->getPrice() ,
                'Опис' => $product->getDescription(),
                'Категорія' => !is_null($product->getCategory()) ? $product->getCategory()->getTitle() : '',
                'Користувач' => $product->getUser()->getName(),
                'Телефони користувач' => $product->getUser()->getPhone(), //TODO телефоны поменять
                'Тип' => $product->getType(),
                'Органічний товар' => $product->getIsOrganicAndApproved() ? 'Так' : 'Ні',
                'Активний' => $product->getIsActive() ? 'Так' : 'Ні',
                'Активний з' => $product->getActiveFrom(),
                'Активний по' => $product->getActiveTo()
            ];
        }

        $exportService->export('commodityProduct', $exportData);
    }
    /**
     * Get product allowed types.
     *
     * @param   Request $request    Request.
     *
     * @return  Response            Response.
     */
    public function getProductAllowedTypes(Request $request): Response
    {
        /** @var User|null $user */
        $requestData    = $request->request->all();
        $userId         = (int) ($requestData['user'] ?? 0);
        $user           = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);

        if (!$user) {
            return new JsonResponse([
                'success'   => false,
                'message'   => 'user was not received',
            ], 404);
        }

        return new JsonResponse([
            'success'   => true,
            'types'     => $this->productAllowedTypesProvider->get($user),
        ]);
    }
    /**
     * @inheritDoc
     */
    protected function updateFormOptions(KeyValueStore $formOptions): void
    {
        parent::updateFormOptions($formOptions);
        $this->addFormParameters($formOptions, [
            'data-commodity-product-form'           => 'Y',
            'data-get-product-allowed-types-url'    => $this->container->get(AdminUrlGenerator::class)
                ->setController(static::class)
                ->setAction('getProductAllowedTypes')
                ->generateUrl(),
        ]);
    }
}
