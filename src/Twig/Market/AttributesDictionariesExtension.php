<?php
declare(strict_types = 1);

namespace App\Twig\Market;

use Doctrine\ORM\EntityManagerInterface;
use Twig\{
    TwigFunction,
    Extension\AbstractExtension,
};
use App\Entity\Market\Attribute;
/**
 * Market, attributes(type dictionary), dictionaries set provider.
 *
 * @package App\Twig
 */
class AttributesDictionariesExtension extends AbstractExtension
{
    private EntityManagerInterface  $entityManager;
    private ?array                  $attributesDictionaries = null;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    /**
     * @inheritDoc
     */
    public function getFunctions(): array
    {
        return array_merge(parent::getFunctions(), [
            new TwigFunction('getAttributesDictionaries', [$this, 'getAttributesDictionaries']),
        ]);
    }
    /**
     * Get dictionaries set.
     *
     * Find all attributes type dictionary. Get dictionaries set for all of them.
     * Dictionaries set: key is dictionary uniquer ID, value is dictionary set, where
     * key is ID and value is printable value
     *
     * @return array                        Dictionaries set.
     */
    public function getAttributesDictionaries(): array
    {
        if (!$this->attributesDictionaries) {
            $this->attributesDictionaries = $this->entityManager
                ->getRepository(Attribute::class)
                ->getAllDictionaries();
        }

        return $this->attributesDictionaries;
    }
}
