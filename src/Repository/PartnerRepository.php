<?php

namespace App\Repository;

use App\Entity\Item;
use App\Entity\Partner;
use App\Entity\TypePage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use function count;
use function in_array;
use function is_array;

/**
 * @method Partner|null find($id, $lockMode = null, $lockVersion = null)
 * @method Partner|null findOneBy(array $criteria, array $orderBy = null)
 * @method Partner[]    findAll()
 * @method Partner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PartnerRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $entityManager;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Partner::class);
        $this->entityManager = $entityManager;
    }

    /**
     * @param $typeFilter
     * @param $locale
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
     * @return int|mixed|string
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function findPartnersIdsWithActiveItems()
    {
        $sqlRaw = 'SELECT DISTINCT ip.partner_id FROM item_partner AS ip LEFT JOIN item i ON ip.item_id = i.id WHERE i.is_active = true';

        $em = $this->getEntityManager();
        $statement = $em->getConnection()->prepare($sqlRaw);
        $statement->execute();
        $result = $statement->fetchAll();

        $ids = [];
        foreach ($result as $item) {
            $ids[] = $item['partner_id'];
        }

        return $this->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }

    public function getPartnerListForFrontPage()
    {
        return $this->createQueryBuilder('p')
            ->where('p.isShownOnFront = true')
            ->getQuery()
            ->getResult();
    }

    public function findAllBySlug(string $slug)
    {
        return $this->createQueryBuilder('p')
            ->where('p.slug LIKE :slug')
            ->setParameter('slug', $slug . '%')
            ->getQuery()
            ->getResult();
    }
}
