<?php

namespace App\Repository;

use App\Entity\Crop;
use App\Entity\Item;
use App\Entity\TypePage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use function count;
use function in_array;
use function is_array;

/**
 * @method Crop|null find($id, $lockMode = null, $lockVersion = null)
 * @method Crop|null findOneBy(array $criteria, array $orderBy = null)
 * @method Crop[]    findAll()
 * @method Crop[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CropRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Crop::class);
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
     * Get available values for list display.
     *
     * @return array                        List data, key => value pairs, where
     *                                      key is item ID and
     *                                      value is item title.
     */
    public function getAsDictionaryListData(): array
    {
        $query  = $this->createQueryBuilder('c')
            ->select('c.id, t.name')
            ->join('c.translations', 't')
            ->andWhere('t.locale = :locale')
            ->setParameter('locale', 'uk')
            ->orderBy('t.name', 'asc')
            ->getQuery()
            ->getArrayResult();
        $result = [];

        foreach ($query as $item) {
            $result[$item['id']] = $item['name'];
        }

        return $result;
    }
}
