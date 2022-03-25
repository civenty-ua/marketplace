<?php
declare(strict_types = 1);

namespace App\Repository\Market;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Entity\Market\Attribute;
/**
 * @method Attribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attribute[]    findAll()
 * @method Attribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attribute::class);
    }
    /**
     * Get all dictionaries.
     *
     * @return array                        Dictionaries set, where
     *                                      key is dictionary name, and
     *                                      value is dictionary map.
     */
    public function getAllDictionaries(): array
    {
        $entityManager          = $this->getEntityManager();
        $dictionaryAttributes   = $this->findBy([
            'type' => Attribute::TYPE_DICTIONARY,
        ]);
        $result                 = [];

        foreach ($dictionaryAttributes as $attribute) {
            if (!isset($result[$attribute->getDictionary()])) {
                $result[$attribute->getDictionary()] = $attribute->loadDictionaryList($entityManager);
            }
        }

        return $result;
    }
}
