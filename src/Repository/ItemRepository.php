<?php

namespace App\Repository;

use App\Entity\{Course, Item, Article, Category, Occurrence, Other, TypePage, Webinar};
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use function count;
use function Doctrine\ORM\QueryBuilder;
use function in_array;

/**
 * @method Item|null find($id, $lockMode = null, $lockVersion = null)
 * @method Item|null findOneBy(array $criteria, array $orderBy = null)
 * @method Item[]    findAll()
 * @method Item[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ItemRepository extends ServiceEntityRepository
{
    private int $maxCount;
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        $this->maxCount = 20;
        parent::__construct($registry, Item::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $searchString
     * @param $locale
     *
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function search(?string $searchString, $locale): array
    {
        if (!$searchString) {
            return [];
        }

        $ids = $this->getResultSearchIds($searchString, $locale);

        return $this->createQueryBuilder('i')
            ->where('i.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->orderBy('i.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param string $searchString
     * @param $locale
     *
     * @return array
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    private function getResultSearchIds(string $searchString, $locale): array
    {
        $sqlRaw = $this->createRawSqlAll($searchString, $locale);
        $em = $this->getEntityManager();
        $statement = $em->getConnection()->prepare($sqlRaw);
        $statement->execute();
        $result = $statement->fetchAll();

        $ids = [];
        foreach ($result as $item) {
            $ids[] = $item['id'];
        }

        return $ids;
    }

    /**
     * @param $locale
     * @param string $searchString
     *
     * @return string
     */
    private function createRawSqlAll(string $searchString, $locale): string
    {
        $sqlRaw = "SELECT item.id FROM item
        LEFT JOIN article ON item.id = article.id
        LEFT JOIN article_translation as art ON article.id = art.translatable_id and art.locale = '$locale'
        LEFT JOIN webinar ON item.id = webinar.id
        LEFT JOIN webinar_translation as wbn ON webinar.id = wbn.translatable_id and wbn.locale = '$locale'
        LEFT JOIN occurrence ON item.id = occurrence.id
        LEFT JOIN occurrence_translation as oct ON occurrence.id = oct.translatable_id and oct.locale = '$locale'
        LEFT JOIN news ON item.id = news.id
        LEFT JOIN news_translation as newst ON news.id = newst.translatable_id and newst.locale = '$locale'
        LEFT JOIN course ON item.id = course.id
        LEFT JOIN course_translation as crs ON course.id = crs.translatable_id and crs.locale = '$locale'
        LEFT JOIN other ON item.id = other.id
        LEFT JOIN other_translation as oth ON other.id = oth.translatable_id and oth.locale = '$locale'
        LEFT JOIN tags ON item.id = tags.id
        LEFT JOIN tags_translation as tg ON tags.id = tg.translatable_id and tg.locale = '$locale'
        LEFT JOIN crop ON item.id = crop.id
        LEFT JOIN crop_translation as crp ON crop.id = crp.translatable_id and crp.locale = '$locale'
        WHERE 1";

        if ($searchString) {

            $searchString = trim($searchString);
            $locale === 'en'
                ? $searchString = str_replace('\'', '\'\'', $searchString)
                : $searchString = str_replace('\'', 'ʼ', $searchString);

            $sqlRaw = $sqlRaw . ' AND item.is_active = true AND (' .
                " art.title LIKE '%" . $searchString . "%' OR" .
                " art.content LIKE '%" . $searchString . "%' OR" .
                " wbn.title LIKE '%" . $searchString . "%' OR" .
                " wbn.content LIKE '%" . $searchString . "%' OR" .
                " oct.title LIKE '%" . $searchString . "%' OR" .
                " oct.content LIKE '%" . $searchString . "%' OR" .
                " newst.title LIKE '%" . $searchString . "%' OR" .
                " newst.content LIKE '%" . $searchString . "%' OR" .
                " oth.title LIKE '%" . $searchString . "%' OR" .
                " oth.content LIKE '%" . $searchString . "%' OR" .
                " crs.title LIKE '%" . $searchString . "%' OR" .
                " crs.content LIKE '%" . $searchString . "%' OR" .
                " tg.name LIKE '%" . $searchString . "%' OR" .
                " crp.name LIKE '%" . $searchString . "%'" .
                ')';
        }

        return $sqlRaw;
    }

    /**
     * @return int|mixed|string
     */
    public function getTopItems(?int $id = null)
    {
        $q = $this->createQueryBuilder('i')
            ->andWhere('i.top = true')
            ->andWhere('i.isActive = true');
        if ($id) {
            $q->andWhere('i.id != :id')
                ->setParameter('id', $id);
        }
        $q->orderBy('i.createdAt', 'DESC')
            ->setMaxResults($this->maxCount);
        return $q->getQuery()
            ->getResult();
    }

    /**
     * @param Category|null $category
     * @return Query
     */
    public function getAllItems(?Category $category = null): Query
    {
        $queryBuilder = $this
            ->createQueryBuilder('i')
            ->andWhere('i.isActive = true');

        if ($category) {
            $queryBuilder
                ->andWhere('i.category = :category')
                ->setParameter('category', $category);
        }

        return $queryBuilder
            ->orderBy('i.id', 'DESC')
            ->getQuery();
    }

    /**
     * @return int|mixed|string
     */
    public function getAllItemsById($id)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.isActive = true')
            ->leftJoin('i.partners', 'p')
            ->where('p.partner = (:id)')
            ->setParameter('id', $id)
            ->orderBy('i.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllItemsByTypePage(array $typePages = [TypePage::TYPE_ARTICLE], bool $isActive = true): array
    {
        $query = $this->entityManager->getRepository(Article::class)->createQueryBuilder('i');
        $pageTypeRep = $this->entityManager->getRepository(TypePage::class);
        $count = count($typePages);

        $query->select('i.id')
            ->andWhere($query->expr()->eq('i.isActive', ':isActive'))
            ->setParameter('isActive', $isActive)
            ->andWhere(
                $query->expr()->eq('i.typePage', $pageTypeRep->findOneBy(['code' => $typePages[0]])->getId())
            );
        if ($count > 1) {
            for ($i = 1; $i < $count; $i++) {
                $query->orWhere(
                    $query->expr()->eq('i.typePage', $pageTypeRep->findOneBy(['code' => $typePages[$i]])->getId())
                );
            }
        }

        $result = $query
            ->orderBy('i.createdAt','DESC')
            ->getQuery()->getResult();

        return array_column($result, 'id');
    }

    /**
     * @param $categoryFilter
     * @param $cropFilter
     * @param $partnerFilter
     * @param $expertFilter
     * @param $typeFilter
     * @param $sortBy
     * @param $search
     * @param $locale
     * @param $typePage
     *
     * @return Query
     */
    public function getFilteredItems(
        $categoryFilter,
        $cropFilter,
        $partnerFilter,
        $expertFilter,
        $typeFilter,
        $sortBy,
        $search,
        $locale,
        $typePage
    ): Query
    {
        $query = $this->createQueryBuilder('i')
            ->addSelect('e')
            ->leftJoin('i.experts', 'e')
            ->addSelect('p')
            ->leftJoin('i.partners', 'p')
            ->addSelect('c')
            ->leftJoin('i.category', 'c')
            ->addSelect('cr')
            ->leftJoin('i.crops', 'cr');

        $types = null;
        $successStoriesId = [];
        $articlesId = [];
        if ($typeFilter) {
            if (in_array('success_stories', $typeFilter, true)) {
                $successStoriesId = $this->findAllItemsByTypePage([TypePage::TYPE_SUCCESS_STORIES]);
                unset($typeFilter[array_search('success_stories', $typeFilter, true)]);
            }
            if (in_array('article', $typeFilter, true)) {
                $articlesId = $this->findAllItemsByTypePage([TypePage::TYPE_ARTICLE, TypePage::TYPE_ECO_ARTICLES]);
                unset($typeFilter[array_search('article', $typeFilter, true)]);
            }
            if (count($typeFilter)) {
                $types = Item::getItemTypeByFilterName($typeFilter);
            }

        }

        $orX = $query->expr()->orX();

        if ($types) {
            foreach ($types as $type) {
                $orX->add($query->expr()->isInstanceOf('i', $type));
            }
        }

        if (count($successStoriesId)) {
            $orX->add($query->expr()->in('i.id', $successStoriesId));
        }
        if (count($articlesId)) {
            $orX->add($query->expr()->in('i.id', $articlesId));
        }

        if ($types === null && empty($successStoriesId) && empty($articlesId)) {
            $types = Item::getItemTypeByFilterName(['course', 'webinar', 'article', 'other', 'occurrence', 'news']);
            foreach ($types as $type) {
                $orX->add($query->expr()->isInstanceOf('i', $type));
            }
        }

        $query->andWhere($orX);

        if ($categoryFilter) {
            if ($types || count($successStoriesId)) {
                $query->andWhere(
                    $query->expr()->andX(
                        'c.id in (:category)',
                        $orX
                    )
                );
            } else {
                $query->andWhere('c.id in (:category)');
            }
            $query->setParameter('category', $categoryFilter);
        }

        if ($cropFilter) {
            if ($types || count($successStoriesId)) {
                $query->andWhere(
                    $query->expr()->andX(
                        'cr.id in (:crop)',
                        $orX
                    )
                );
            } else {
                $query->andWhere('cr.id in (:crop)');
            }
            $query->setParameter('crop', $cropFilter);
        }

        if ($partnerFilter) {
            if ($types || count($successStoriesId)) {
                $query->andWhere(
                    $query->expr()->andX(
                        'p.id in (:partners)',
                        $orX
                    )
                );
            } else {
                $query->andWhere('p.id in (:partners)');
            }
            $query->setParameter('partners', $partnerFilter);
        }

        if ($expertFilter) {
            if ($types || count($successStoriesId)) {
                $query->andWhere(
                    $query->expr()->andX(
                        'e.id in (:experts)',
                        $orX
                    )
                );
            } else {
                $query->andWhere('e.id in (:experts)');
            }
            $query->setParameter('experts', $expertFilter);
        }

        if (!empty(trim($search))) {
            try {
                $ids = $this->getResultSearchIds($search, $locale);
            } catch (Exception|\Doctrine\DBAL\Exception $e) {
                $ids = [];
            }
            $query->andWhere('i.id IN (:ids)')
                ->setParameter('ids', $ids);
        }


        if ($sortBy) {
            $query->orderBy("i.$sortBy", 'DESC');
        } else {
            $query->orderBy('i.viewsAmount', 'DESC');
        }

        $query->andWhere('i.isActive = true');
        return $query->getQuery()->setQueryCacheLifetime(600);
    }

    public function getItemsForCalendar()
    {
        $em = $this->getEntityManager();

        $dql = <<<DQL
            SELECT i FROM App\Entity\Item AS i
            LEFT JOIN i.partners AS p
            WHERE (i INSTANCE OF :type OR  i INSTANCE OF :typetwo
            OR i INSTANCE OF :typethree) AND i.isActive = true AND i.startDate is not null
            DQL;
        $q = $em->createQuery($dql);
        $q->setParameter('type', $em->getClassMetadata(Course::class));
        $q->setParameter('typetwo', $em->getClassMetadata(Webinar::class));
        $q->setParameter('typethree', $em->getClassMetadata(Occurrence::class));
//        $q->setParameter('typethree', $em->getClassMetadata(News::class)); //todo uncoment this when creat news Etnity
        return $q->getResult();
    }

    public function countActiveItems()
    {
        $data['coursesCount'] = 0;
        $data['articlesCount'] = 0;
        $data['webinarsCount'] = 0;
        $data['othersCount'] = 0;
        $data['occurrenceCount'] = 0;
        $data['newsCount'] = 0;

        $em = $this->getEntityManager();

        $sqlRaw = 'SELECT count(i.id) AS icount,discr FROM item AS i WHERE i.is_active = true GROUP BY i.discr';
        $statement = $em->getConnection()->prepare($sqlRaw);
        $statement->execute();
        $result = $statement->fetchAll();

        foreach ($result as $item) {
            switch ($item['discr']) {
                case ('article'):
                    $data['articlesCount'] = $item['icount'];
                    break;
                case('webinar'):
                    $data['webinarsCount'] = $item['icount'];
                    break;
                case('course'):
                    $data['coursesCount'] = $item['icount'];
                    break;
                case('other'):
                    $data['othersCount'] = $item['icount'];
                    break;
                case('occurrence'):
                    $data['occurrenceCount'] = $item['icount'];
                    break;
                case('news'):
                    $data['newsCount'] = $item['icount'];
                    break;
            }
        }

        return $data;
    }

    public function findAllBySlug(string $slug)
    {
        return $this->createQueryBuilder('i')
            ->where('i.slug LIKE :slug')
            ->setParameter('slug', $slug . '%')
            ->getQuery()
            ->getResult();
    }
}
