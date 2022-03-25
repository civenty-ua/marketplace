<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Expert;
use App\Entity\Item;
use App\Entity\TypePage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use function count;
use function in_array;
use function is_array;

/**
 * @method Expert|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expert|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expert[]    findAll()
 * @method Expert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpertRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Expert::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @param $typeFilter
     * @return int|mixed|string
     */
    public function getAllSorted($typeFilter)
    {
        $query = $this->createQueryBuilder('c')
            ->addSelect('COUNT(i.id), i.isActive')
            ->join('c.items', 'i')
            ->having('COUNT(i.id) > 0')
            ->addGroupBy('c.id')
            ->addOrderBy('COUNT(i.id)', 'DESC');

        $types = null;
        $successStoriesId = [];
        $articlesId = [];
        if (is_array($typeFilter)) {

            if (in_array('success_stories', $typeFilter, true)) {
                $successStoriesId = $this->entityManager->getRepository(Item::class)->findAllItemsByTypePage([TypePage::TYPE_SUCCESS_STORIES]);
                unset($typeFilter['success_stories']);
            }
            if (in_array('article', $typeFilter, true)) {
                $articlesId = $this->entityManager->getRepository(Item::class)->findAllItemsByTypePage([TypePage::TYPE_ARTICLE, TypePage::TYPE_ECO_ARTICLES]);
                unset($typeFilter['article']);
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
            $types = Item::getItemTypeByFilterName(['course', 'webinar', 'article', 'other', 'occurrence']);
            foreach ($types as $type) {
                $orX->add($query->expr()->isInstanceOf('i', $type));
            }
        }

        $query->andWhere($orX);

        $query->andWhere('i.isActive = true');

        return $query
            ->getQuery()
            ->enableResultCache(3600)
            ->getResult();
    }

    /**
     * @param $tagFilter
     *
     * @return Query
     */
    public function getFilterExperts($tagFilter): Query
    {
        $query = $this->createQueryBuilder('e')
            ->distinct()
            ->join('e.translations', 'tr');

        $orX = $query->expr()->orX();

        if ($tagFilter) {
            $query->join('e.tags', 't');
            $query->orWhere(
                $query->expr()->andX(
                    't.id in (:tags)',
                    $orX
                )
            );
            $query->setParameter('tags', $tagFilter);
        }
        $query->andWhere("tr.locale = 'uk'");

        return $query->getQuery();
    }

    public function findAllBySlug(string $slug)
    {
        return $this->createQueryBuilder('e')
            ->where('e.slug LIKE :slug')
            ->setParameter('slug', $slug . '%')
            ->getQuery()
            ->getResult();
    }
}
