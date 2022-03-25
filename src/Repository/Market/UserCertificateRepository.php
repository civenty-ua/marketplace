<?php

namespace App\Repository\Market;

use App\Entity\Market\CategoryAttributeParameters;
use App\Entity\Market\UserCertificate;
use App\Entity\User;
use App\Repository\Market\Exception\CommoditiesEmptyFilterException;
use App\Repository\Traits\FilterApplierTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserCertificate|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserCertificate|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserCertificate[]    findAll()
 * @method UserCertificate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCertificateRepository extends ServiceEntityRepository
{
    use FilterApplierTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserCertificate::class);
    }

    public static function getSortList()
    {
        return [
           /* 'ecology',*/
            'create',
            'name'
        ];
    }

    /**
     * List filter provider.
     *
     * @param User|null $user Current user.
     * @param string $order Order.
     * @param array $filter Filter.
     * @param array $filterAttributes Filter (attributes).
     * @param CategoryAttributeParameters[] $attributesParameters Category attributes parameters.
     *
     * @return  QueryBuilder                                            Query builder.
     */
    public function listFilter(
        string $order,
        array  $filter = []
    ): QueryBuilder
    {
        $alias = 'cert';
        $queryBuilder = $this
            ->createQueryBuilder($alias);
        $this->querySelect($queryBuilder, $alias, $filter['search'] ?? '');


        $this->Filter($queryBuilder, $alias, $filter);

        $this->applyListOrder($queryBuilder,$order);

        return $queryBuilder;
    }

    private function querySelect(QueryBuilder $queryBuilder, string $alias, string $needle): void
    {
        if (empty($needle)) return;

        $queryBuilder->where("$alias.name LIKE :query")->setParameter('query', "%$needle%");
    }

    private function Filter(QueryBuilder $queryBuilder, string $alias, array $filter): void
    {
        if (empty($filter)) return;

        if($filter['user'])
        {
            $queryBuilder
                ->leftJoin("$alias.userProperty", "up")
                ->leftJoin("up.user",'u')
                ->andWhere("u.id = :userId")
                ->setParameter('userId', $filter['user'])
            ;
        }
    }
    /**
     * Apply products list order.
     *
     * @param   QueryBuilder    $queryBuilder   Query builder.
     * @param   string          $order          Order.
     *
     * @return  void
     */
    private function applyListOrder(QueryBuilder $queryBuilder, string $order): void
    {
        $alias = $queryBuilder->getRootAliases()[0];

        switch ($order) {
            case 'create':
                $queryBuilder->orderBy("$alias.createdAt", 'desc');
                break;
            case 'ecology':
                $queryBuilder->orderBy("$alias.isEcology", 'desc');
                break;
            case 'name':
                $queryBuilder->orderBy("$alias.name", 'asc');
                break;
            default:
                $queryBuilder->orderBy("$alias.id", 'desc');
        }
    }
}
