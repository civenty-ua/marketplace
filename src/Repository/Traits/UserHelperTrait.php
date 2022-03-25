<?php
declare(strict_types = 1);

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Query\Expr\Join;
use App\Repository\Helper\AliasMap;
use App\Entity\{
    Region,
    User,
    Market\UserCertificate,
    Market\UserFavorite,
    Market\UserProperty,
    Market\Phone,
};
/**
 * User repository helper trait.
 */
trait UserHelperTrait
{
    /**
     * Apply user sub-entities data query.
     *
     * @param   QueryBuilder    $queryBuilder   Query builder.
     * @param   AliasMap        $aliasMap       Alias map.
     *
     * @return  void
     */
    protected function applyUserSubEntitiesQuery(QueryBuilder $queryBuilder, AliasMap $aliasMap): void
    {
        $userAlias = $aliasMap->getAlias(User::class);

        $aliasMap
            ->setAlias(Phone::class, 'userPhone')
            ->setAlias(Region::class, 'userRegion')
            ->setAlias(UserProperty::class, 'userProperty')
            ->setAlias(UserCertificate::class, 'userCertificates');

        $queryBuilder
            ->leftJoin("$userAlias.phones", $aliasMap->getAlias(Phone::class))
            ->addSelect($aliasMap->getAlias(Phone::class))
            ->leftJoin("$userAlias.region", $aliasMap->getAlias(Region::class))
            ->addSelect($aliasMap->getAlias(Region::class))
            ->leftJoin("$userAlias.userProperty", $aliasMap->getAlias(UserProperty::class))
            ->addSelect($aliasMap->getAlias(UserProperty::class))
            ->leftJoin(
                "{$aliasMap->getAlias(UserProperty::class)}.userCertificates",
                $aliasMap->getAlias(UserCertificate::class)
            )
            ->addSelect($aliasMap->getAlias(UserCertificate::class));
    }
    /**
     * Apply users favorites query.
     *
     * @param   QueryBuilder    $queryBuilder   Query builder.
     * @param   User|null       $user           User, if exists.
     * @param   AliasMap        $aliasMap       Alias map.
     *
     * @return  void
     */
    protected function applyUsersFavoritesQuery(
        QueryBuilder    $queryBuilder,
        ?User           $user,
        AliasMap        $aliasMap
    ): void {
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $aliasMap->setAlias(UserFavorite::class, 'commodityFavorite');

        $queryBuilder
            ->leftJoin(
                "$rootAlias.favorites",
                $aliasMap->getAlias(UserFavorite::class),
                Join::WITH,
                "{$aliasMap->getAlias(UserFavorite::class)}.user = :currentUser"
            )
            ->addSelect($aliasMap->getAlias(UserFavorite::class))
            ->setParameter('currentUser', $user ? $user->getId() : 0);
    }
    /**
     * Apply market user activity filter.
     *
     * @param   QueryBuilder $queryBuilder      Query builder.
     *
     * @return  void
     */
    protected function applyMarketUserActivityFilter(QueryBuilder $queryBuilder): void
    {
        $rootAlias  = $queryBuilder->getRootAliases()[0];
        $filters    = $this->getMarketUserActivityFilterConditions($rootAlias, [
            User::ROLE_WHOLESALE_BUYER,
            User::ROLE_SALESMAN,
            User::ROLE_SERVICE_PROVIDER,
        ]);

        foreach ($filters as $filter) {
            $queryBuilder->andWhere($filter['query']);
            foreach ($filter['parameters'] as $key => $value) {
                $queryBuilder->setParameter($key, $value);
            }
        }
    }
    /**
     * Apply market user inactivity filter.
     *
     * @param   QueryBuilder $queryBuilder      Query builder.
     *
     * @return  void
     */
    protected function applyMarketUserInactivityFilter(QueryBuilder $queryBuilder): void
    {
        $rootAlias  = $queryBuilder->getRootAliases()[0];
        $filters    = $this->getMarketUserInactivityFilterConditions($rootAlias, [
            User::ROLE_WHOLESALE_BUYER,
            User::ROLE_SALESMAN,
            User::ROLE_SERVICE_PROVIDER,
        ]);
        $queryParts = [];

        foreach ($filters as $filter) {
            $queryParts[] = $filter['query'];

            foreach ($filter['parameters'] as $key => $value) {
                $queryBuilder->setParameter($key, $value);
            }
        }

        $queryBuilder->andWhere(implode(' OR ', $queryParts));
    }
    /**
     * Get user activity filters set.
     *
     * @param   string      $alias          Alias.
     * @param   string[]    $roles          User required roles.
     *
     * @return  array                       Filters set.
     */
    protected function getMarketUserActivityFilterConditions(string $alias, array $roles = []): array
    {
        $result = [
            [
                'query'         => "$alias.isVerified = :isVerified",
                'parameters'    => ['isVerified' => 1],
            ],
            [
                'query'         => "$alias.isBanned = :isBanned OR $alias.isBanned IS NULL",
                'parameters'    => ['isBanned' => 0],
            ],
        ];

        if (count($roles) > 0) {
            $queryParts = [];
            $parameters = [];

            foreach ($roles as $index => $role) {
                $parameterName              = "userRole_$index";
                $queryParts[]               = "$alias.roles LIKE :$parameterName";
                $parameters[$parameterName] = "%\"$role\"%";
            }

            $result[] = [
                'query'         => implode(' OR ', $queryParts),
                'parameters'    => $parameters,
            ];
        }

        return $result;
    }
    /**
     * Get user inactivity filters set.
     *
     * @param   string      $alias          Alias.
     * @param   string[]    $roles          User required roles.
     *
     * @return  array                       Filters set.
     */
    protected function getMarketUserInactivityFilterConditions(string $alias, array $roles = []): array
    {
        $result = [
            [
                'query'         => "$alias.isVerified = :isVerified OR $alias.isVerified IS NULL",
                'parameters'    => ['isVerified' => 0],
            ],
            [
                'query'         => "$alias.isBanned = :isBanned",
                'parameters'    => ['isBanned' => 1],
            ],
        ];

        if (count($roles) > 0) {
            $queryParts = [];
            $parameters = [];

            foreach ($roles as $index => $role) {
                $parameterName              = "userRole_$index";
                $queryParts[]               = "$alias.roles NOT LIKE :$parameterName";
                $parameters[$parameterName] = "%\"$role\"%";
            }

            $queryFull  = implode(' AND ', $queryParts);
            $result[]   = [
                'query'         => "($queryFull)",
                'parameters'    => $parameters,
            ];
        }

        return $result;
    }
}
