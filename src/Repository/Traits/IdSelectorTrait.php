<?php
declare(strict_types = 1);

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

use function array_unique;
/**
 * ID selector helper.
 *
 * Provide functionality for entities ID query.
 */
trait IdSelectorTrait
{
    /**
     * Run query and get only ID set.
     *
     * @param   QueryBuilder $queryBuilder  Query builder.
     *
     * @return  int[]                       ID set.
     */
    protected function queryIdSet(QueryBuilder $queryBuilder): array
    {
        $alias  = $queryBuilder->getRootAliases()[0];
        $result = [];

        $queryBuilder->select("$alias.id");
        foreach ($queryBuilder->getQuery()->getResult() as $item) {
            $result[] = $item['id'];
        }

        return array_unique($result);
    }
}
