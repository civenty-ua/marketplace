<?php
declare(strict_types=1);

namespace App\Controller\Admin\Market;

use UnexpectedValueException;
use DateTime;
use ReflectionClass;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    RequestStack,
    Response,
    JsonResponse,
    RedirectResponse,
    Exception\BadRequestException,
};
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\{
    FormBuilderInterface,
    FormEvent,
    FormEvents,
};
use EasyCorp\Bundle\EasyAdminBundle\{
    Config\Action,
    Config\Actions,
    Config\Crud,
    Config\KeyValueStore,
    Context\AdminContext,
    Dto\EntityDto,
    Router\AdminUrlGenerator,
};
use App\Service\Market\{
    CommodityDatesCalculator,
    ProductAllowedTypesProvider,
    CommodityActivity\CommodityActivityManager,
};
use App\Event\Commodity\{
    Admin\CommodityAdminCreateEvent,
    Admin\CommodityAdminUpdateEvent,
    Admin\CommodityAdminActivationEvent,
    Admin\CommodityAdminDeactivationEvent,
};
use App\Entity\{
    User,
    Market\Category,
    Market\Commodity,
    Market\CommodityAttributeValue,
};
/**
 * ProductCrudController.
 */
abstract class CommodityCrudController extends MarketCrudController
{
    protected TranslatorInterface $translator;
    protected EventDispatcherInterface $eventDispatcher;
    protected AdminUrlGenerator $adminUrlGenerator;
    protected CommodityDatesCalculator $commodityDatesCalculator;
    protected ProductAllowedTypesProvider $productAllowedTypesProvider;
    protected CommodityActivityManager $commodityActivityManager;
    protected ?string $currentLocale;
    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     * @param EventDispatcherInterface $eventDispatcher
     * @param AdminUrlGenerator $adminUrlGenerator
     * @param CommodityDatesCalculator $commodityDatesCalculator
     * @param ProductAllowedTypesProvider $productAllowedTypesProvider
     * @param CommodityActivityManager $commodityActivityManager
     * @param RequestStack $requestStack
     */
    public function __construct(
        TranslatorInterface $translator,
        EventDispatcherInterface $eventDispatcher,
        AdminUrlGenerator $adminUrlGenerator,
        CommodityDatesCalculator $commodityDatesCalculator,
        ProductAllowedTypesProvider $productAllowedTypesProvider,
        CommodityActivityManager $commodityActivityManager,
        RequestStack  $requestStack
    ) {
        $this->translator = $translator;
        $this->eventDispatcher = $eventDispatcher;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->commodityDatesCalculator = $commodityDatesCalculator;
        $this->productAllowedTypesProvider = $productAllowedTypesProvider;
        $this->commodityActivityManager = $commodityActivityManager;
        $this->currentLocale = $requestStack->getCurrentRequest()->getLocale();
    }
    /**
     * @inheritdoc
     */
    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['id' => 'desc'])
            ->setSearchFields(['title']);
    }
    /**
     * @inheritdoc
     */
    public function configureActions(Actions $actions): Actions
    {
        $getCommodityActiveToAction = Action::new('getCommodityActiveToDate')
            ->linkToCrudAction('getCommodityActiveToDate')
            ->setHtmlAttributes([
                'style' => 'display: none;',
            ]);

        return parent::configureActions($actions)
            ->add(
                Crud::PAGE_EDIT,
                Action::new('toggleCommodityIsActive', 'Активувати/Деактивувати')
                    ->linkToCrudAction('toggleCommodityIsActive')
                    ->addCssClass('btn btn-primary')
            )
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->add(Crud::PAGE_NEW, $getCommodityActiveToAction)
            ->add(Crud::PAGE_EDIT, $getCommodityActiveToAction);
    }
    /**
     * @inheritdoc
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Commodity $entityInstance */
        $this->setCommodityAttributesValues(
            $entityManager,
            $entityInstance,
            $this->getContext()->getRequest()
        );

        parent::persistEntity($entityManager, $entityInstance);

        $event = new CommodityAdminCreateEvent();
        $event->setCommodity($entityInstance);
        $this->eventDispatcher->dispatch($event);
    }
    /**
     * @inheritdoc
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Commodity $entityInstance */
        $this->setCommodityAttributesValues(
            $entityManager,
            $entityInstance,
            $this->getContext()->getRequest()
        );

        parent::updateEntity($entityManager, $entityInstance);

        $event = new CommodityAdminUpdateEvent();
        $event->setCommodity($entityInstance);
        $this->eventDispatcher->dispatch($event);
    }

    public function toggleCommodityIsActive(): RedirectResponse
    {
        $entity = $this->getContext()->getEntity()->getInstance();
        $this->toggleIsActive($entity);
        return $this->redirect($this->getContext()->getRequest()->headers->get('referer'));
    }

    public function toggleIsActive(Commodity $entity)
    {
        if ($entity->getIsActive() === true) {
            $event = new CommodityAdminDeactivationEvent();
        } else {
            if ($this->checkIfCommodityCanBeActivatedForUser($entity)) {
                $event = new CommodityAdminActivationEvent();
            } else {
              $this->addFlash('error', 'Користувачу не можно активувати ще сутності. Його ліміт ='
                    . $entity->getUser()->getUserProperty()->getAllowedAmountOfSellingCommodities());
              return  $this->redirect($this->getContext()->getRequest()->headers->get('referer'));
            }
        }
        $event->setCommodity($entity);
        $event->setAdmin($this->getUser());
        $this->eventDispatcher->dispatch($event);
    }
    /**
     * @inheritDoc
     */
    public function createNewFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createNewFormBuilder($entityDto, $formOptions, $context);
        $this->prepareCommodityFormBuilder($formBuilder);

        return $formBuilder;
    }
    /**
     * @inheritDoc
     */
    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $formBuilder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $this->prepareCommodityFormBuilder($formBuilder);

        return $formBuilder;
    }

    /**
     * Get commodity active to date.
     *
     * @param   Request $request    Request.
     *
     * @return  Response            Response.
     */
    public function getCommodityActiveToDate(Request $request): Response
    {
        /**
         * @var User|null       $user
         * @var Commodity|null  $commodity
         */
        $requestData        = $request->request->all();
        $controllerClass    = (string)  ($requestData['controller'] ?? '');
        $userId             = (int)     ($requestData['user']       ?? 0);
        $commodityId        = (int)     ($requestData['commodity']  ?? 0);
        $commodityClass     = $controllerClass::getEntityFqcn();
        $user               = $this->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        $commodity          = $commodityId > 0
            ? $this->getDoctrine()->getRepository($commodityClass)->find($commodityId)
            : new $commodityClass();

        if (!$user) {
            return new JsonResponse([
                'success'   => false,
                'message'   => 'user was not received',
            ], 404);
        }
        if (!$commodity) {
            return new JsonResponse([
                'success'   => false,
                'message'   => 'commodity was not found',
            ], 404);
        }

        try {
            $commodity->setUser($user);
            $activeToDate = $this->commodityDatesCalculator->getCommodityActiveToDate($commodity);

            return new JsonResponse([
                'success'   => true,
                'date'      => $activeToDate,
            ]);
        } catch (UnexpectedValueException $exception) {
            return new JsonResponse([
                'success'   => false,
                'message'   => $exception->getMessage(),
            ], 500);
        }
    }
    /**
     * @inheritDoc
     */
    protected function updateFormOptions(KeyValueStore $formOptions): void
    {
        parent::updateFormOptions($formOptions);
        $this->addFormParameters($formOptions, [
            'data-commodity-form'           => 'Y',
            'data-get-active-to-date-url'   => $this->container->get(AdminUrlGenerator::class)
                ->setController(static::class)
                ->setAction('getCommodityActiveToDate')
                ->generateUrl(),
        ]);
    }
    /**
     * Prepare commodity form builder.
     *
     * @param   FormBuilderInterface $formBuilder   Form builder.
     *
     * @return  void
     */
    private function prepareCommodityFormBuilder(FormBuilderInterface $formBuilder): void
    {
        $formBuilder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                /** @var Commodity|null $commodity */
                $commodity = $event->getData();

                if (!$commodity) {
                    return;
                }

                if (!$commodity->getActiveFrom()) {
                    $commodity->setActiveFrom(new DateTime('now'));
                }
            });
    }

    private function checkIfCommodityCanBeActivatedForUser(Commodity $commodity): bool
    {
        $currentTime = new \DateTime('now');
        return ($commodity->getActiveTo() >= $currentTime &&
            $this->checkAllowedUserCommodityAmountNotOverflow($commodity->getUser()));
    }

    private function checkAllowedUserCommodityAmountNotOverflow(User $user): bool
    {
        $activeUserCommodityAmount = $this->getDoctrine()->getRepository(Commodity::class)->getTotalCount($this->getUser(), [
            'user'          => $user,
        ]);
        return $activeUserCommodityAmount < $user->getUserProperty()->getAllowedAmountOfSellingCommodities();
    }
    /**
     * Set commodity attributes values data.
     *
     * @param EntityManagerInterface $entityManager Entity manager.
     * @param Commodity $commodity Commodity.
     * @param Request $request Request.
     *
     * @return  void
     * @throws  BadRequestException                     Post data process error.
     */
    private function setCommodityAttributesValues(
        EntityManagerInterface $entityManager,
        Commodity              $commodity,
        Request                $request
    ): void {
        $commodityClassName = (new ReflectionClass($commodity))->getShortName();
        $postData = (array)($request->request->all()[$commodityClassName] ?? []);
        $categoryId = (int)($postData['category'] ?? 0);
        $incomeAttributesValues = (array)($postData['commodityAttributesValues'] ?? []);
        /** @var Category|null $category */
        $category = $entityManager
            ->getRepository(Category::class)
            ->find($categoryId);
        $existAttributesValues = [];
        $categoryAttributes = [];

        foreach ($commodity->getCommodityAttributesValues() as $attributeValue) {
            $existAttributesValues[$attributeValue->getAttribute()->getId()] = $attributeValue;
        }

        if ($category) {
            foreach ($category->getCategoryAttributesParameters() as $attributeParameters) {
                $attributeId = $attributeParameters->getAttribute()->getId();
                $attributeTitle = $attributeParameters->getAttribute()->getTitle();
                $incomeData = $incomeAttributesValues[$attributeId] ?? null;

                if ($attributeParameters->getRequired() && empty($incomeData)) {
                    throw new BadRequestException("missing required attribute \"$attributeTitle\"");
                }

                $categoryAttributes[$attributeId] = $attributeParameters->getAttribute();
            }
        }

        foreach ($incomeAttributesValues as $attributeId => $value) {
            if (isset($existAttributesValues[$attributeId])) {
                $attributeValue = $existAttributesValues[$attributeId];
                unset($existAttributesValues[$attributeId]);
            } else {
                $attributeValue = (new CommodityAttributeValue())
                    ->setCommodity($commodity)
                    ->setAttribute($categoryAttributes[$attributeId]);
            }

            $attributeValue->setValue($value);
            $commodity->addCommodityAttributeValue($attributeValue);
            $entityManager->persist($attributeValue);
        }

        foreach ($existAttributesValues as $attributeValue) {
            $commodity->removeCommodityAttributeValue($attributeValue);
        }
    }
}