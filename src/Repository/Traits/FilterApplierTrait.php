<?php
declare(strict_types = 1);

namespace App\Repository\Traits;

use InvalidArgumentException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\{
    Parameter,
    Expr\Join,
};

use function is_string;
use function is_array;
use function is_null;
use function strlen;
use function count;
use function in_array;
use function array_search;
use function array_map;
use function rand;
/**
 * Repository filter applier.
 *
 * Provides helpful methods to apply filters in different ways.
 */
trait FilterApplierTrait
{
    /**
     * Apply filter to query builder.
     *
     * @param   QueryBuilder    $builder    Query builder.
     * @param   string          $alias      Table alias.
     * @param   array           $filter     Filter.
     *
     * @return  void
     */
    protected function applyFilter(QueryBuilder $builder, string $alias, array $filter): void
    {
        foreach ($filter as $key => $value) {
            if (is_array($value) && isset($value['field']) && isset($value['join'])) {
                $joinAlias = "{$value['field']}Join";
                $builder->join(
                    $value['join'],
                    $joinAlias,
                    Join::WITH,
                    "$joinAlias.id = $alias.{$value['field']}"
                );
            } elseif (is_array($value) && isset($value['field'])) {
                if (!is_string($value['field']) || strlen($value['field']) === 0) {
                    throw new InvalidArgumentException('key "field" must contains string');
                }
                if (isset($value['condition']) && !is_string($value['condition'])) {
                    throw new InvalidArgumentException('key "condition" must contains string');
                }

                if (isset($value['condition'])) {
                    $this->applyComplexFilter(
                        $builder,
                        $alias,
                        $value['field'],
                        $value['condition'],
                        $value['value'] ?? null
                    );
                } else {
                    $this->applySimpleFilter(
                        $builder,
                        $alias,
                        $value['field'],
                        $value['value'] ?? null
                    );
                }
            } else {
                $this->applySimpleFilter(
                    $builder,
                    $alias,
                    (string) $key,
                    $value
                );
            }
        }
    }
    /**
     * Apply simple filtration.
     *
     * @param   QueryBuilder    $builder    Query builder.
     * @param   string          $alias      Table alias.
     * @param   string          $field      Field.
     * @param   mixed           $value      Value.
     *
     * @return  void
     */
    private function applySimpleFilter(
        QueryBuilder    $builder,
        string          $alias,
        string          $field,
        $value
    ): void {
        if (is_array($value) && in_array(null, $value)) {
            unset($value[array_search(null, $value)]);

            $parameterAlias = $this->buildParameterAlias($builder, $field);

            $builder
                ->andWhere("($alias.$field IN (:$parameterAlias) OR $alias.$field IS NULL)")
                ->setParameter($parameterAlias, $value);
        } elseif (is_null($value)) {
            $this->applyComplexFilter($builder, $alias, $field, 'isNull');
        } else {
            $this->applyComplexFilter($builder, $alias, $field, '=', $value);
        }
    }
    /**
     * Apply complex filtration.
     *
     * @param   QueryBuilder    $builder    Query builder.
     * @param   string          $alias      Table alias.
     * @param   string          $field      Field.
     * @param   string          $condition  Condition.
     * @param   mixed           $value      Value.
     *
     * @return  void
     */
    private function applyComplexFilter(
        QueryBuilder    $builder,
        string          $alias,
        string          $field,
        string          $condition,
        $value = null
    ): void {
        switch ($condition) {
            case 'between':
                if (!is_array($value) || !isset($value[0]) || !isset($value[1])) {
                    throw new InvalidArgumentException('"between" condition must be passed with two values');
                }

                $fromAlias  = $this->buildParameterAlias($builder, "{$field}From");
                $toAlias    = $this->buildParameterAlias($builder, "{$field}To");

                $builder
                    ->andWhere("$alias.$field BETWEEN :$fromAlias AND :$toAlias")
                    ->setParameter($fromAlias, $value[0])
                    ->setParameter($toAlias, $value[1]);
                break;
            case '<':
            case '<=':
            case '>':
            case '>=':
                $parameterAlias = $this->buildParameterAlias($builder, $field);

                $builder
                    ->andWhere("$alias.$field $condition :$parameterAlias")
                    ->setParameter($parameterAlias, $value);
                break;
            case '=':
            case '!=':
                if (is_array($value)) {
                    $this->applyComplexFilter(
                        $builder,
                        $alias,
                        $field,
                        $condition === '=' ? 'in' : 'notIn',
                        $value
                    );
                } else {
                    $parameterAlias = $this->buildParameterAlias($builder, $field);

                    $builder
                        ->andWhere("$alias.$field $condition :$parameterAlias")
                        ->setParameter($parameterAlias, $value);
                }
                break;
            case 'isNull':
                $builder->andWhere("$alias.$field IS NULL");
                break;
            case 'isNotNull':
                $builder->andWhere("$alias.$field IS NOT NULL");
                break;
            case 'in':
                if (count($value) > 0) {
                    $parameterAlias = $this->buildParameterAlias($builder, $field);

                    $builder
                        ->andWhere("$alias.$field IN (:$parameterAlias)")
                        ->setParameter($parameterAlias, $value);
                }
                break;
            case 'notIn':
                if (count($value) > 0) {
                    $parameterAlias = $this->buildParameterAlias($builder, $field);

                    $builder
                        ->andWhere("$alias.$field NOT IN (:$parameterAlias)")
                        ->setParameter($parameterAlias, $value);
                }
                break;
            default:
        }
    }
    /**
     * Build unique parameter alias.
     *
     * @param   QueryBuilder    $builder    Query builder.
     * @param   string          $field      Field name.
     *
     * @return  string                      Field parameter unique value.
     */
    private function buildParameterAlias(QueryBuilder $builder, string $field): string
    {
        $existParameters = array_map(function(Parameter $parameter): string {
            return $parameter->getName();
        }, $builder->getParameters()->toArray());

        while (in_array($field, $existParameters)) {
            $randomNumber   = rand(1000, 3000);
            $field          = "{$field}_$randomNumber";
        }

        return $field;
    }
}
