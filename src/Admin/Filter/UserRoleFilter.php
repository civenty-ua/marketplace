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
class UserRoleFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(
        string $field,
        string $formTypeFqcn,
               $label = null
    ): self
    {

        return (new self())
            ->setFilterFqcn(__CLASS__)
            ->setProperty($field)
            ->setLabel($label)
            ->setFormType($formTypeFqcn)
            ->setFormTypeOption('translation_domain', 'EasyAdminBundle');
    }

    public function apply(
        QueryBuilder  $queryBuilder,
        FilterDataDto $filterDataDto,
        ?FieldDto     $fieldDto,
        EntityDto     $entityDto
    ): void
    {
        $queryBuilder->andWhere("{$filterDataDto->getEntityAlias()}.roles LIKE '%\"{$filterDataDto->getValue()}\"%'");
    }
}
