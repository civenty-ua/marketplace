<?php

namespace App\Repository\Traits;

use Throwable;
use DateInterval;
use DatePeriod;
use DateTime;

use function array_merge;
use function count;
use function key;
use function reset;
/**
 * Repository count per date trait.
 *
 * Provides method for getting items count per created date.
 * WARNING: entity must have createdAt field!
 */
trait CountPerCreatedDateTrait
{
    private static string $tableAlias   = 'table';
    private static string $dateFormat   = 'Y-m-d';
    /**
     * Get items count per time.
     *
     * @param   DateTime    $from           Date from.
     * @param   DateTime    $to             Date to.
     * @param   array       $filter         Filter.
     *
     * @return  array                       Data, where
     *                                      key is date and
     *                                      value is items count for that day.
     */
    public function getItemsCountPerCreatedDate(DateTime $from, DateTime $to, array $filter = []): array
    {
        $alias      = self::$tableAlias;
        $builder    = $this
            ->createQueryBuilder($alias)
            ->select("$alias.createdAt");
        $fullPeriod = new DatePeriod($from, new DateInterval('P1D'), $to);
        $result     = [];

        foreach ($fullPeriod as $date) {
            $dateFormatted          = $date->format(self::$dateFormat);
            $result[$dateFormatted] = 0;
        }
        if (isset($filter['isBanned'])) {
            $isBanned = $filter['isBanned'];
            unset($filter['isBanned']);
        }
        if (isset($filter['instance'])) {
            $instance = $filter['instance'];
            unset($filter['instance']);
        }
        if (isset($filter['!roles'])) {
            $roles = $filter['!roles'];
            unset($filter['!roles']);
        }
        $this->applyFilter($builder, $alias, array_merge($filter, [[
            'field'     => 'createdAt',
            'condition' => 'between',
            'value'     => [$from, $to],
        ]]));
        if (isset($instance) && $instance === 'user') {
            if (isset($isBanned)) {
                $builder->andWhere(
                    $builder->expr()->eq("$alias.isBanned", ':isBanned')
                );
                $builder->setParameter('isBanned', $isBanned);
            }
            if (isset($roles) && count($roles)) {
                $andX = $builder->expr()->andX();
                foreach ($roles as $role) {
                    $andX->add($builder->expr()->notLike("$alias.roles", "'%$role%'"));
                }
                $builder->andWhere($andX);
            }
        }
        foreach ($builder->getQuery()->getResult() as $item) {
            /** @var DateTime $date */
            $date           = $item['createdAt'];
            $dateFormatted  = $date->format(self::$dateFormat);
            $result[$dateFormatted]++;
        }

        reset($result);
        $result[key($result)] += $this->getItemsCountBeforeDate($from);

        return $result;
    }
    /**
     * Get items count before date.
     *
     * @param   DateTime    $date           Date.
     *
     * @return  int                         Items count.
     */
    private function getItemsCountBeforeDate(DateTime $date): int
    {
        $alias      = self::$tableAlias;
        $builder    = $this
            ->createQueryBuilder($alias)
            ->select("count($alias.id)");

        $this->applyFilter($builder, $alias, [[
            'field'     => 'createdAt',
            'condition' => '<',
            'value'     => $date,
        ]]);

        try {
            return $builder
                ->getQuery()
                ->getSingleScalarResult();
        } catch (Throwable $exception) {
            return 0;
        }
    }
}