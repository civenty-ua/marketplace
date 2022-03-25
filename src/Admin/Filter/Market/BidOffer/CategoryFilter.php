<?php

namespace App\Admin\Filter\Market\BidOffer;

use App\Entity\Market\CommodityProduct;
use App\Entity\Market\CommodityService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\{EntityDto, FieldDto, FilterDataDto,};
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;
use Symfony\Contracts\Translation\TranslatorInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use App\Admin\Field\Market\CategoryField;
use function count;

class CategoryFilter implements FilterInterface
{
    use FilterTrait;

    private static TranslatorInterface $translator;
    private static Registry $em;

    public static function new(TranslatorInterface $translator, Registry $em): self
    {
        self::$translator = $translator;
        self::$em = $em;

        $choices = [];

        foreach (CategoryField::getCategoriesTree($em) as $category) {
            $choices[CategoryField::makeCategoryTreeTitle($category)] = $category->getId();
        }
        return (new self())
            ->setProperty('category')
            ->setFilterFqcn(__CLASS__)
            ->setFormType(ChoiceFilterType::class)
            ->setFormTypeOption('value_type_options', [
                'choices'   => $choices,
                'multiple'  => true,
            ]);
    }

    public function apply(
        QueryBuilder    $queryBuilder,
        FilterDataDto   $filterDataDto,
        ?FieldDto       $fieldDto,
        EntityDto       $entityDto
    ): void {
        $categoryId             = $filterDataDto->getValue();
        $commoditiesInCategory  = [];

        foreach ([
            CommodityProduct::class,
            CommodityService::class,
        ] as $repository) {
            $commodityQueryBuilder = self::$em->getRepository($repository)->createQueryBuilder('commodity');
            $categoriesId          = $commodityQueryBuilder
                ->select('commodity.id')
                ->join('commodity.category', 'category')
                ->where(
                    $commodityQueryBuilder->expr()->in('category.id', $categoryId)
                )
                ->getQuery()
                ->getResult();

            foreach ($categoriesId as $item) {
                $commoditiesInCategory[] = $item['id'];
            }
        }

        $commoditiesInCategory = array_unique($commoditiesInCategory);
        $queryBuilder
            ->join("{$filterDataDto->getEntityAlias()}.commodity", 'commodity');
            switch ($filterDataDto->getComparison()) {
                case 'IN':
                    $queryBuilder->andWhere(
                        $queryBuilder->expr()->in('commodity.id', count($commoditiesInCategory) > 0 ? $commoditiesInCategory : ['none'])
                    );
                    break;
                case 'NOT IN':
                    $queryBuilder->andWhere(
                        $queryBuilder->expr()->notIn('commodity.id', count($commoditiesInCategory) > 0 ? $commoditiesInCategory : ['none'])
                    );
                    break;
                default:
            }
    }
}
