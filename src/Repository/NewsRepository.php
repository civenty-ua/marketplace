<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Category;
use App\Entity\News;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Traits\{
    FilterApplierTrait,
    CountPerCreatedDateTrait,
};
use App\Entity\Article;

/**
 * @method News|null find($id, $lockMode = null, $lockVersion = null)
 * @method News|null findOneBy(array $criteria, array $orderBy = null)
 * @method News[]    findAll()
 * @method News[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsRepository extends ServiceEntityRepository
{
    use FilterApplierTrait;
    use CountPerCreatedDateTrait;

    private const NEWS_ALIAS = 'news';
    private const REGIONS_ALIAS = 'regions';
    private const TRANSLATIONS_ALIAS = 'translations';

    private int $maxCount;

    public function __construct(ManagerRegistry $registry)
    {
        $this->maxCount = 9;
        parent::__construct($registry, News::class);
    }

    /**
     * @return int|mixed|string
     */
    public function getSimilar(array $data)
    {

        $q = $this->createQueryBuilder('n')
            ->andWhere('n.isActive = true');
        if (array_key_exists('cropsIds', $data)) {
            $q->leftJoin('n.crops', 'cr')
                ->andWhere('cr.id IN (:cropsIds)')
                ->setParameter('cropsIds', $data['cropsIds']);
        }
        if (array_key_exists('categoryId', $data)) {
            $q->leftJoin('n.category', 'cat')
                ->andWhere('cat.id = :categoryId')
                ->setParameter('categoryId', $data['categoryId']);
        }
        if (array_key_exists('id', $data)) {
            $q->andWhere('n.id != :id')
                ->setParameter('id', $data['id']);
        }


        $q->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($this->maxCount);

        return $q->getQuery()->getResult();
    }

    /**
     * @param $pageType
     * @param bool $isActive
     * @return int
     * @throws NonUniqueResultException
     */
    public function getCountArticle($pageType, bool $isActive = true): int
    {
        return $this->createQueryBuilder('a')
            ->select('count(a.id) as count')
            ->andWhere('a.typePage = :typePage')
            ->andWhere('a.isActive = :isActive')
            ->setParameter('typePage', $pageType)
            ->setParameter('isActive', $isActive)
            ->getQuery()
            ->getOneOrNullResult()['count'];
    }


    /**
     * @return int|mixed|string
     */
    public function getTopArticles() //todo change this to improve forming collection of similar articles
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.top = true')
            ->andWhere('i.isActive = true')
            ->orderBy('i.id', 'DESC')
            ->setMaxResults($this->maxCount)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get items count per regions.
     *
     * @param array $filter Articles filter.
     *
     * @return  array                       Data, where
     *                                      key is region ID and
     *                                      value is region articles count
     */
    public function getItemsPerRegionsCount(array $filter = []): array
    {
        /** @var Article[] $articles */
        $regionsAlias = self::REGIONS_ALIAS;
        $articlesAlias = self::NEWS_ALIAS;
        $articlesBuilder = $this
            ->createQueryBuilder($articlesAlias)
            ->leftJoin("$articlesAlias.region", $regionsAlias)
            ->select([
                "$articlesAlias.id as article",
                "$regionsAlias.id as region",
            ]);
        $result = [];

        foreach ($filter as $key => $value) {
            $condition = is_array($value)
                ? "$articlesAlias.$key IN (:$key)"
                : "$articlesAlias.$key = :$key";

            $articlesBuilder->andWhere($condition);
            $articlesBuilder->setParameter($key, $value);
        }

        $articlesData = $articlesBuilder
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        foreach ($articlesData as $articleData) {
            $regionId = (int)$articleData['region'];
            $result[$regionId] = $result[$regionId] ?? 0;
            $result[$regionId]++;
        }

        return $result;
    }

    /**
     * Get items by filter parameters
     *
     * @param array $filter Filter.
     * @param string $locale Locale.
     * @param array $order Order.
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return  News[]                   Articles set.
     */
    public function findByAlt(
        array  $filter,
        string $locale,
        array  $order = [],
        ?int   $limit = null,
        ?int   $offset = null
    ): array
    {
        $articlesAlias = self::NEWS_ALIAS;
        $translationsAlias = self::TRANSLATIONS_ALIAS;
        $articlesBuilder = $this
            ->createQueryBuilder(self::NEWS_ALIAS)
            ->andWhere("$articlesAlias.isActive = true")
            ->join("$articlesAlias.translations", $translationsAlias);

        foreach ($filter as $key => $value) {
            switch ($key) {
                case 'content':
                    $articlesBuilder->andWhere("
                    (
                        (
                            $translationsAlias.title LIKE :content OR
                            $translationsAlias.content LIKE :content
                        ) AND
                        $translationsAlias.locale = :locale
                    )");
                    $articlesBuilder->setParameter('content', "%$value%");
                    $articlesBuilder->setParameter('locale', $locale);
                    break;
                default:
                    if (is_array($value) && in_array(null, $value)) {
                        unset($value[array_search(null, $value)]);

                        $articlesBuilder->andWhere("
                        (
                            $articlesAlias.$key IN (:$key) OR
                            $articlesAlias.$key IS NULL
                        )
                        ");
                        $articlesBuilder->setParameter($key, $value);
                    } elseif (is_array($value)) {
                        $articlesBuilder->andWhere("$articlesAlias.$key IN (:$key)");
                        $articlesBuilder->setParameter($key, $value);
                    } else {
                        $articlesBuilder->andWhere("$articlesAlias.$key = :$key");
                        $articlesBuilder->setParameter($key, $value);
                    }
            }
        }

        if (count($order) > 0) {
            $sortValue = array_key_first($order);
            $order = array_values($order)[0];

            switch ($sortValue) {
                case 'title':
                    $sort = "$translationsAlias.title";
                    break;
                default:
                    $sort = "$articlesAlias.$sortValue";
            }

            $articlesBuilder->orderBy($sort, $order);
        }

        if ($limit) {
            $articlesBuilder->setMaxResults($limit);
        }
        if ($offset) {
            $articlesBuilder->setFirstResult($offset);
        }

        return $articlesBuilder
            ->getQuery()
            ->getResult();
    }

    public function getNewsByCategory(Category $category)
    {
       return $this->createQueryBuilder('n')
            ->where('n.isActive = true')
            ->andWhere('n.category = :cat')
            ->setParameter('cat',$category)
            ->setMaxResults(20)
            ->getQuery()->getResult();
    }

    public function getLastModified()
    {
        return $this->createQueryBuilder('n')
            ->select('n.updatedAt')
            ->orderBy('n.updatedAt', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
