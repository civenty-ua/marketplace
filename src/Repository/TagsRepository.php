<?php

namespace App\Repository;

use App\Entity\Tags;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tags|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tags|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tags[]    findAll()
 * @method Tags[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TagsRepository extends ServiceEntityRepository
{
    private const LIMIT_TAGS = 30;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tags::class);
    }

    public function getTopAll($locale): array //todo change getters of topTags in Twig
    {
        $sqlRaw = "SELECT count(item_tags.item_id) as count_tag, tags.id, tags.slug, tags_tr.name  FROM item_tags
        LEFT JOIN tags on item_tags.tags_id = tags.id
        LEFT JOIN tags_translation as tags_tr ON tags.id = tags_tr.translatable_id and tags_tr.locale = '$locale'
        GROUP BY tags_tr.name, tags.id
        ORDER BY count(item_tags.item_id) DESC
        LIMIT " . $this::LIMIT_TAGS;

        $em = $this->getEntityManager();
        $statement = $em->getConnection()->prepare($sqlRaw);
        $statement->execute();
        $results = $statement->fetchAll();

        $tagList = [];
        foreach ($results as $item) {
            $tagList[] = [
                'id' => $item['id'],
                'slug' => $item['slug'],
                'name' => $item['name'],
                'count_tag' => $item['count_tag'],
            ];
        }

        return $tagList;
    }

    public function getTopArticleTags($locale): array
    {
        $sqlRaw = "SELECT count(item_tags.item_id) as count_tag, tags.id, tags.slug, tags_tr.name  FROM item_tags
        LEFT JOIN item on item_tags.item_id = item.id
        LEFT JOIN tags on item_tags.tags_id = tags.id
        LEFT JOIN tags_translation as tags_tr ON tags.id = tags_tr.translatable_id and tags_tr.locale = '$locale'
        WHERE item.discr ='article'
        GROUP BY tags_tr.name, tags.id
        ORDER BY count(item_tags.item_id) DESC
        LIMIT " . $this::LIMIT_TAGS;

        $em = $this->getEntityManager();
        $statement = $em->getConnection()->prepare($sqlRaw);
        $statement->execute();
        $results = $statement->fetchAll();

        $tagList = [];
        foreach ($results as $item) {
            $tagList[] = [
                'id' => $item['id'],
                'slug' => $item['slug'],
                'name' => $item['name'],
                'count_tag' => $item['count_tag'],
            ];
        }

        return $tagList;
    }

    public function findAllBySlug(string $slug)
    {
        return $this->createQueryBuilder('t')
            ->where('t.slug LIKE :slug')
            ->setParameter('slug', $slug . '%')
            ->getQuery()
            ->getResult();
    }
}
