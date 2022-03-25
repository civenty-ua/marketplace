<?php
declare(strict_types=1);

namespace App\Entity\Market;

use InvalidArgumentException;
use Doctrine\ORM\{
    Mapping as ORM,
    EntityManagerInterface,
};
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\Market\AttributeRepository;
use App\Entity\Crop;
/**
 * @ORM\Entity(repositoryClass=AttributeRepository::class)
 * @ORM\Table(name="market_category_attribute")
 */
class Attribute
{
    public const TYPE_STRING = 'string';
    public const TYPE_INT = 'int';
    public const TYPE_LIST = 'list';
    public const TYPE_LIST_MULTIPLE = 'listMultiple';
    public const TYPE_DICTIONARY = 'dictionary';

    public const DICTIONARIES_DATA = [
        'crop' => [
            'entity'                => Crop::class,
            'repositoryProvider'    => 'getAsDictionaryListData',
        ],
    ];

    public const SPECIAL_CODE_VOLUME = 'volume';
    public const SPECIAL_CODE_MEASURE = 'measure';
    public const SPECIAL_CODE_PRICE_MEASURE = 'price_measure';
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(
     *      min = 2,
     *      max = 255,
     *      minMessage = "Title attribute must be at least {{ limit }} characters long",
     *      maxMessage = "Title attribute cannot be longer than {{ limit }} characters"
     * )
     * @Assert\Type("string")
     */
    private ?string $title = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Assert\Type("string")
     * @Assert\Length(
     *      max = 255,
     *      maxMessage = "Description cannot be longer than {{ limit }} characters"
     * )
     */
    private ?string $description = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $type = self::TYPE_STRING;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $code = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $dictionary = null;

    /**
     * Get available attribute types.
     *
     * @return string[]                     Types set.
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_STRING,
            self::TYPE_INT,
            self::TYPE_LIST,
            self::TYPE_LIST_MULTIPLE,
            self::TYPE_DICTIONARY,
        ];
    }
    /**
     * Get available special codes set.
     *
     * @return string[]                     Codes set.
     */
    public static function getAvailableSpecialCodes(): array
    {
        return [
            self::SPECIAL_CODE_VOLUME,
            self::SPECIAL_CODE_MEASURE,
            self::SPECIAL_CODE_PRICE_MEASURE,
        ];
    }

    public function __toString(): string
    {
        return $this->getTitle() ?? 'attribute[no title]';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        if (!in_array($type, self::getAvailableTypes())) {
            throw new InvalidArgumentException("unknown type $type");
        }

        $this->type = $type;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        if ($code && !in_array($code, self::getAvailableSpecialCodes())) {
            throw new InvalidArgumentException("unknown special code $code");
        }

        $this->code = $code;

        return $this;
    }

    public function getDictionary(): ?string
    {
        return $this->dictionary;
    }

    public function setDictionary(?string $dictionary): self
    {
        if ($this->getType() !== self::TYPE_DICTIONARY) {
            return $this;
        }
        if (!isset(self::DICTIONARIES_DATA[$dictionary])) {
            throw new InvalidArgumentException("unknown dictionary $dictionary");
        }

        $this->dictionary = $dictionary;

        return $this;
    }
    /**
     * Load dictionary list.
     *
     * @param   EntityManagerInterface $entityManager   Entity manager.
     *
     * @return  array                                   Dictionary list, where
     *                                                  key is choice ID and
     *                                                  value is choice title.
     */
    public function loadDictionaryList(EntityManagerInterface $entityManager): array
    {
        if ($this->getType() !== self::TYPE_DICTIONARY) {
            return [];
        }

        $dictionaryParameters   = self::DICTIONARIES_DATA[$this->getDictionary()]   ?? [];
        $dictionaryEntity       = $dictionaryParameters['entity']                   ?? '';
        $repositoryProvider     = $dictionaryParameters['repositoryProvider']       ?? '';

        return strlen($dictionaryEntity) && strlen($repositoryProvider) > 0
            ? $entityManager->getRepository($dictionaryEntity)->$repositoryProvider()
            : [];
    }
}
