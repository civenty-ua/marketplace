<?php

namespace App\Repository;

use App\Entity\Options;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Options|null find($id, $lockMode = null, $lockVersion = null)
 * @method Options|null findOneBy(array $criteria, array $orderBy = null)
 * @method Options[]    findAll()
 * @method Options[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Options::class);
    }

    /**
     * @param $codes
     * @return array|string|null
     */
    public function getByCode($codes)
    {
        $options = null;

        $queryBuilder = $this
            ->createQueryBuilder('o')
            ->select('o.code, o.value')
        ;

        if (!is_array($codes)) {
            $codes = [$codes];
        }

        $queryBuilder->where($queryBuilder->expr()->in('o.code', $codes));

        $result = $queryBuilder
            ->getQuery()
            ->getArrayResult();

        if ($result) {
            if (count($result) === 1) {
                $options = $result[0]['value'];
            } else {
                foreach ($result as $option) {
                    $options[$option['code']] = $option['value'];
                }

            }
        }

        return $options;
    }
}
