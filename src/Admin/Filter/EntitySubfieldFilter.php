<?php

namespace App\Admin\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;
use EasyCorp\Bundle\EasyAdminBundle\Dto\{
    EntityDto,
    FieldDto,
    FilterDataDto,
};
/**
 * Custom entity subfield filter.
 */
class EntitySubfieldFilter implements FilterInterface
{
    use FilterTrait;

    private static string $property;
    private static string $subProperty;

    public static function new(
        string  $field,
        string  $property,
        string  $subProperty,
        string  $formTypeFqcn,
        $label = null
    ): self {
        self::$property     = $property;
        self::$subProperty  = $subProperty;

        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($field)
            ->setLabel($label)
            ->setFormType($formTypeFqcn)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle');
    }

    public function apply(
        QueryBuilder    $queryBuilder,
        FilterDataDto   $filterDataDto,
        ?FieldDto       $fieldDto,
        EntityDto       $entityDto
    ): void {
        $property       = self::$property;
        $subProperty    = self::$subProperty;

        $queryBuilder
            ->join("{$filterDataDto->getEntityAlias()}.$property", $property)
            ->andWhere("$property.$subProperty = :$subProperty")
            ->setParameter($subProperty, $filterDataDto->getValue());
    }
}
