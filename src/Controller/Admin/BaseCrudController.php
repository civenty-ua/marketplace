<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use Doctrine\ORM\QueryBuilder;
use Throwable;
use ReflectionException;
use ReflectionClass;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use EasyCorp\Bundle\EasyAdminBundle\{
    Context\AdminContext,
    Controller\AbstractCrudController,
};
use EasyCorp\Bundle\EasyAdminBundle\{Collection\FieldCollection,
    Config\Action,
    Config\Actions,
    Config\Assets,
    Config\Crud,
    Config\KeyValueStore,
    Factory\EntityFactory,
    Factory\FilterFactory,
    Router\AdminUrlGenerator};
/**
 * BaseCrudController.
 */
abstract class BaseCrudController extends AbstractCrudController
{
    public const URL_FILTER_KEY = 'filters';
    /**
     * @inheritdoc
     */
    public function configureAssets(Assets $assets): Assets
    {
        return $assets->addWebpackEncoreEntry('admin');
    }
    /**
     * @inheritdoc
     */
    public function new(AdminContext $context)
    {
        $formOptions = $context->getCrud()->getNewFormOptions();

        if ($formOptions) {
            $this->updateFormOptions($formOptions);
        }

        return parent::new($context);
    }
    /**
     * @inheritdoc
     */
    public function edit(AdminContext $context)
    {
        $formOptions = $context->getCrud()->getEditFormOptions();

        if ($formOptions) {
            $this->updateFormOptions($formOptions);
        }

        return parent::edit($context);
    }
    /**
     * @inheritdoc
     */
    public function configureActions(Actions $actions): Actions
    {
        $refreshFormAction = Action::new('refreshForm')
            ->linkToCrudAction('refreshForm')
            ->setHtmlAttributes([
                'style' => 'display: none;',
            ]);

        return parent::configureActions($actions)
            ->update(Crud::PAGE_INDEX,
                Action::EDIT,
                function (Action $action) {
                    return $action
                        ->setIcon('fas fa-edit')
                        ->setLabel(false)
                        ->setHtmlAttributes([
                            'placeholder' => 'Редагувати',
                        ]);
                })
            ->update(Crud::PAGE_INDEX,
                Action::DELETE,
                function (Action $action) {
                    return $action
                        ->setIcon('fas fa-trash-alt')
                        ->setLabel(false)
                        ->setHtmlAttributes([
                            'placeholder' => 'Видалити',
                        ]);
                })
            ->add(Crud::PAGE_NEW, $refreshFormAction)
            ->add(Crud::PAGE_EDIT, $refreshFormAction);
    }
    /**
     * Refresh form action.
     *
     * @param   Request $request    Request.
     *
     * @return  Response            Response.
     */
    public function refreshForm(Request $request): Response
    {
        $context    = $this->getContext();
        $newEntity  = $this->createEntity($context->getEntity()->getFqcn());

        try {
            $context->getEntity()->setInstance($newEntity);
            $this->container->get(EntityFactory::class)->processFields(
                $context->getEntity(),
                FieldCollection::new($this->configureFields(Crud::PAGE_NEW))
            );
        } catch (Throwable $exception) {
            return new JsonResponse([
                'success'   => false,
                'message'   => $exception->getMessage(),
            ], 500);
        }

        $form = $this->createNewForm(
            $context->getEntity(),
            $context->getCrud()->getNewFormOptions(),
            $context
        );
        $form->handleRequest($request);

        return new JsonResponse([
            'success'   => true,
            'form'      => $this->renderView('admin/form.html.twig', [
                'form' => $form->createView(),
            ])
        ]);
    }
    /**
     * Set default filter for index page.
     *
     * @param   AdminContext    $context    Context.
     * @param   string          $field      Field name.
     * @param   mixed           $value      Filter value.
     *
     * @return  void
     */
    protected function setIndexPageDefaultFilter(AdminContext $context, string $field, $value): void
    {
        $this->addCrudFilter($context, $field);
        $this->modifyRequestFilter($context, $field, $value);
        $this->modifyCrudInnerFilter($context, $field, $value);
    }
    /**
     * Add to CRUD property filter, if not exist yet.
     *
     * @param   AdminContext    $context    Context.
     * @param   string          $field      Field name.
     *
     * @return  void
     */
    protected function addCrudFilter(AdminContext $context, string $field): void
    {
        $filtersConfig = $context->getCrud()->getFiltersConfig();

        if (!isset($filtersConfig->all()[$field])) {
            $filtersConfig->addFilter($field);
        }
    }
    /**
     * Modify request filters data with new one.
     *
     * @param   AdminContext    $context    Context.
     * @param   string          $field      Field name.
     * @param   mixed           $value      Filter value.
     *
     * @return  void
     */
    protected function modifyRequestFilter(AdminContext $context, string $field, $value): void
    {
        $requestQuery       = $context->getRequest()->query;
        $existUrlFilters    = $requestQuery->get(self::URL_FILTER_KEY) ?? [];

        $requestQuery->set(self::URL_FILTER_KEY, array_merge($existUrlFilters, [
            $field => $value,
        ]));
    }
    /**
     * Modify CRUD inner filters data with new one.
     *
     * @param   AdminContext    $context    Context.
     * @param   string          $field      Field name.
     * @param   mixed           $value      Filter value.
     *
     * @return  void
     */
    protected function modifyCrudInnerFilter(AdminContext $context, string $field, $value): void
    {
        try {
            $contextSearch          = $context->getSearch();
            $contextSearchProperty  = (new ReflectionClass($contextSearch))->getProperty('appliedFilters');

            $contextSearchProperty->setAccessible(true);
            $searchExistValues = $contextSearchProperty->getValue($contextSearch);
            $contextSearchProperty->setValue($contextSearch, array_merge($searchExistValues, [
                $field => $value,
            ]));
            $contextSearchProperty->setAccessible(false);
        } catch (ReflectionException $exception) {

        }
    }

    protected function updateFormOptions(KeyValueStore $formOptions): void
    {
        $this->addFormParameters($formOptions, [
            'data-refresh-form-url' => $this->container->get(AdminUrlGenerator::class)
                ->setController(static::class)
                ->setAction('refreshForm')
                ->generateUrl(),
        ]);
    }

    protected function addFormParameters(
        KeyValueStore   $formOptions,
        array           $parameters
    ): void {
        $existAttributes = (array) $formOptions->get('attr');

        $formOptions->set('attr', array_merge($existAttributes, $parameters));
    }

    protected function getPageCurrentQueryBuilder($page):QueryBuilder
    {
        $context = $this->getContext();
        $fields = FieldCollection::new($this->configureFields($page));
        $filters = $this->get(FilterFactory::class)->create($context->getCrud()->getFiltersConfig(), $fields, $context->getEntity());
        return $this->createIndexQueryBuilder($context->getSearch(), $context->getEntity(), $fields, $filters);
    }
}
