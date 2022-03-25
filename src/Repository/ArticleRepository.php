<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\TypePage;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\Traits\{
    FilterApplierTrait,
    CountPerCreatedDateTrait,
};
use App\Entity\Article;
use function count;

/**
 * @method Article|null find($id, $lockMode = null, $lockVersion = null)
 * @method Article|null findOneBy(array $criteria, array $orderBy = null)
 * @method Article[]    findAll()
 * @method Article[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArticleRepository extends ServiceEntityRepository
{
    use FilterApplierTrait;
    use CountPerCreatedDateTrait;

    private const ARTICLES_ALIAS = 'articles';
    private const REGIONS_ALIAS = 'regions';
    private const TRANSLATIONS_ALIAS = 'translations';

    private int $maxCount;

    public function __construct(ManagerRegistry $registry)
    {
        $this->maxCount = 9;
        parent::__construct($registry, Article::class);
    }

    /**
     * @param string $code
     *
     * @return int|mixed|string
     */
    public function getSimilar(string $code, array $data)
    {

        $q = $this->createQueryBuilder('a')
            ->leftJoin('a.typePage', 'tp')
            ->andWhere('tp.code = :code')
            ->andWhere('a.isActive = true')
            ->setParameter('code', $code);
        if (array_key_exists('cropsIds', $data)) {
            $q->leftJoin('a.crops', 'cr')
                ->andWhere('cr.id IN (:cropsIds)')
                ->setParameter('cropsIds', $data['cropsIds']);
        }
        if (array_key_exists('categoryId', $data)) {
            $q->leftJoin('a.category', 'cat')
                ->andWhere('cat.id = :categoryId')
                ->setParameter('categoryId', $data['categoryId']);
        }
        if (array_key_exists('id', $data)) {
            $q->andWhere('a.id != :id')
                ->setParameter('id', $data['id']);
        }


        $q->orderBy('a.createdAt', 'DESC')
            ->setMaxResults($this->maxCount);

        return $q->getQuery()->getResult();
    }

    /**
     * @param $pageType
     * @param bool $isActive
     * @return int
     * @throws NonUniqueResultException
     */
    public function getCountArticle(array $pageTypes = [TypePage::TYPE_ARTICLE], bool $isActive = true): int
    {
        $count = count($pageTypes);
        $query = $this->createQueryBuilder('a');
        $query
            ->select('count(a.id) as count')
            ->andWhere(
                $query->expr()->eq('a.typePage', $pageTypes[0])
            );
        if ($count > 1) {
            for ($i = 1; $i < $count; $i++) {
                $query->orWhere(
                    $query->expr()->eq('a.typePage', $pageTypes[$i])
                );
            }
        }
        return $query
            ->andWhere('a.isActive = :isActive')
            ->setParameter('isActive', $isActive)
            ->getQuery()
            ->getOneOrNullResult()['count'];
    }


    /**
     * @return int|mixed|string
     */
    public function getTopArticles() //todo chacnge this to improve forming collection of similar articles
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
        $articlesAlias = self::ARTICLES_ALIAS;
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
     * @return  Article[]                   Articles set.
     */
    public function findByAlt(
        array  $filter,
        string $locale,
        array  $order = [],
        ?int   $limit = null,
        ?int   $offset = null
    ): array
    {
        $articlesAlias = self::ARTICLES_ALIAS;
        $translationsAlias = self::TRANSLATIONS_ALIAS;
        $articlesBuilder = $this
            ->createQueryBuilder(self::ARTICLES_ALIAS)
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

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getCountSuccessesStory()
    {
        return $this->createQueryBuilder('a')
            ->select('count(a.id)')
            ->join('a.typePage', 'pt')
            ->andWhere("pt.code = 'success_stories'")
            ->andWhere('a.isActive = true')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findArticleByTypeName($typeCode, $catId)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.typePage', 'tp')
            ->leftJoin('a.category', 'c')
            ->andWhere('c.id = :catId')
            ->andWhere('tp.code = :code')
            ->andWhere('a.isActive = true')
            ->setParameter('code', $typeCode)
            ->setParameter('catId', $catId)
            ->setMaxResults(20)
            ->orderBy('a.createdAt','DESC')
            ->getQuery()
            ->getResult();
    }

    public function findArticleOnlyByTypeName($typeCode,int $maxCount = null)
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.typePage', 'tp')
            ->andWhere('tp.code = :code')
            ->andWhere('a.isActive = true')
            ->setParameter('code', $typeCode)
            ->setMaxResults($maxCount ? : 20)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get items count per time.
     *
     * @param DateTime $from Date from.
     * @param DateTime $to Date to.
     * @param array $filter Filter.
     *
     * @return  array                       Data, where
     *                                      key is date and
     *                                      value is items count for that day.
     */
    public function getCountPerTime(DateTime $from, DateTime $to, array $filter = []): array
    {
        return $this->getItemsCountPerCreatedDate($from, $to, $filter);
    }
}
