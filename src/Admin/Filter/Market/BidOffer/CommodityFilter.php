<?php

namespace App\Admin\Filter\Market\BidOffer;

use App\Entity\Market\CommodityKit;
use App\Entity\Market\CommodityProduct;
use App\Entity\Market\CommodityService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\{EntityDto, FieldDto, FilterDataDto,};
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommodityFilter implements FilterInterface
{
    use FilterTrait;

    private static TranslatorInterface $translator;

    public static function new(TranslatorInterface $translator): self
    {
        self::$translator = $translator;

        return (new self())
            ->setProperty('commodity')
            ->setFilterFqcn(__CLASS__)
            ->setFormType(ChoiceFilterType::class)
            ->setFormTypeOption('value_type_options', [
                'choices' => [
                    self::$translator->trans('admin.market.product.titles.edit') => CommodityProduct::class,
                    self::$translator->trans('admin.market.service.titles.edit') => CommodityService::class,
                    self::$translator->trans('admin.market.kit.titles.edit') => CommodityKit::class,
                ],
                'multiple' => true,
            ]);
    }

    public function apply(
        QueryBuilder    $queryBuilder,
        FilterDataDto   $filterDataDto,
        ?FieldDto       $fieldDto,
        EntityDto       $entityDto
    ): void {
        $availableTypes = [CommodityProduct::class, CommodityService::class, CommodityKit::class];
        $commodityTypes = $filterDataDto->getValue();
        $orX            = $queryBuilder->expr()->orX();
        switch ($filterDataDto->getComparison()) {
            case 'IN':
                foreach ($commodityTypes as $commodityType) {
                    $orX->add(
                        $queryBuilder->expr()->isInstanceOf('commodity', $commodityType)
                    );
                }
                break;
            case 'NOT IN':
                $commodityTypesIsNeeded = array_diff($availableTypes,$commodityTypes);
                foreach ($commodityTypesIsNeeded as $commodityType) {
                    $orX->add(
                        $queryBuilder->expr()->isInstanceOf('commodity', $commodityType)
                    );
                }
                break;
            default:
        }

        $queryBuilder
            ->join("{$filterDataDto->getEntityAlias()}.commodity", 'commodity')
            ->andWhere($orX);
    }
}
