<?php

namespace App\Controller\Admin\Market;

use App\Admin\Filter\Market\BidOffer\CommodityFilter as BidOfferCommodityFilter;
use App\Admin\Filter\Market\BidOffer\CategoryFilter as BidOfferCategoryFilter;
use App\Entity\Market\CommodityProduct;
use App\Entity\Market\CommodityService;
use App\Entity\Market\Notification\BidOffer;
use App\Service\ExportService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use Symfony\Contracts\Translation\TranslatorInterface;

class AgreementBidOfferCrudController extends MarketCrudController
{

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public static function getEntityFqcn(): string
    {
        return BidOffer::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);

        return $actions
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('export', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('export')
                    ->addCssClass('btn btn-primary')
            );
    }

    /**
     * @inheritdoc
     */
    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->setLabel('#');
        yield AssociationField::new('sender')
            ->setLabel('????????????????????')
            ->setColumns('col-sm-6 col-md-4');
        yield AssociationField::new('receiver')
            ->setLabel('????????????????????????')
            ->setColumns('col-sm-6 col-md-4');
        yield DateTimeField::new('createdAt')
            ->setLabel('????????');
        yield AssociationField::new('commodity')
            ->setLabel('??????????/??????????????/??????????')
            ->setColumns('col-sm-6 col-md-4');
        yield TextareaField::new('commodity')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('??????????????????')
            ->formatValue(function($value, BidOffer $bidOffer) {
                $commodity = $bidOffer->getCommodity();
                if ($commodity instanceof CommodityProduct || $commodity instanceof CommodityService) {
                    return $commodity->getCategory();
                }
                return '';
            });
        yield TextareaField::new('commodity')
            ->setColumns('col-sm-6 col-md-4')
            ->setLabel('??????')
            ->formatValue(function($value, BidOffer $bidOffer) {
                $typeOfCommodity = $bidOffer->getCommodity()->getCommodityType();
                return $this->translator->trans('admin.market.' .$typeOfCommodity. '.titles.edit');
            });
        yield TextareaField::new('message')
            ->setLabel('????????????????????????')
            ->setColumns('col-sm-6 col-md-4')
        ;
    }


    public function getMessagesDomain(): string
    {
        return 'bidOffer';
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields([
                'id',
                'sender.name',
                'receiver.name',
            ]);
    }
    public function configureFilters(Filters $filters): Filters
    {
        return parent::configureFilters($filters)
            ->add(EntityFilter::new('sender')->setLabel('????????????????????'))
            ->add(EntityFilter::new('receiver')->setLabel('????????????????????????'))
            ->add(DateTimeFilter::new('createdAt')->setLabel('????????'))
            ->add(
                BidOfferCommodityFilter::new($this->translator)
                    ->setLabel('??????')
            )
            ->add(
                BidOfferCategoryFilter::new($this->translator, $this->getDoctrine())
                    ->setLabel('??????????????????')
            );
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function export(ExportService $exportService): void
    {
        $query = $this->getPageCurrentQueryBuilder(Crud::PAGE_INDEX);
        $bidOfferList = $query->getQuery()->toIterable();
        $exportData = [];

        /** @var BidOffer $bidOffer */
        foreach ($bidOfferList as $bidOffer) {
            $exportData[] = $this->getGeneralExportBidOfferData($bidOffer);
            $this->getDoctrine()->getManager()->clear();
        }
        if (!empty($exportData)) {
            $exportService->export('bidOffer', $exportData);
        }
    }

    private function getGeneralExportBidOfferData(BidOffer $bidOffer): array
    {
        return [
            'ID' => $bidOffer->getId(),
            '????????????????????' => $bidOffer->getSender()->getName() ?: '????????????????',
            '????????????????????????' => $bidOffer->getReceiver()->getName() ?: '????????????????',
            '????????' => $bidOffer->getCreatedAt(),
            '??????????/??????????????/??????????' => $bidOffer->getCommodity(),
            '??????????????????' => $bidOffer->getCommodity() instanceof CommodityProduct ||
                            $bidOffer->getCommodity() instanceof CommodityService
                            ? $bidOffer->getCommodity()->getCategory()->getTitle()
                            : '????????????????',
            '??????' => $this->translator->trans('admin.market.'.$bidOffer->getCommodity()->getCommodityType().'.titles.edit'),
            '????????????????????????' => $bidOffer->getMessage(),
        ];
    }
}