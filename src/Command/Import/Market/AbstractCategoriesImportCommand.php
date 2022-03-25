<?php

namespace App\Command\Import\Market;

use RuntimeException;
use OutOfBoundsException;
use SplFileInfo;
use PhpOffice\PhpSpreadsheet\{
    Exception as PhpSpreadsheetException,
    IOFactory,
};
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\Market\{
    Attribute,
    Category,
};

use function is_string;
use function strlen;
use function strtolower;
use function implode;
use function trim;
use function rtrim;
use function ucfirst;
use function mb_ereg_replace;
use function count;
use function in_array;
use function array_filter;
use function array_unique;

use const DIRECTORY_SEPARATOR;
/**
 * Abstract import categories command.
 */
abstract class AbstractCategoriesImportCommand extends Command
{
    private const FILE_ALLOWED_EXTENSIONS = [
        'xls',
        'xlsx',
    ];
    private const STRING_ATTRIBUTE_VALUE_MARK       = 'Самостійно';
    private const NUMBER_ATTRIBUTES                 = [
        'Обсяг (вага)',
    ];
    private const DICTIONARY_ATTRIBUTE_VALUE_MARK   = 'Випадаючий список';
    private const WORKABLE_DICTIONARIES_MAP         = [
        'Культура' => 'crop',
    ];

    protected EntityManagerInterface    $entityManager;
    protected ParameterBagInterface     $parameter;

    private array $entitiesBar = [];
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ParameterBagInterface  $parameter
     */
    public function __construct(
        EntityManagerInterface  $entityManager,
        ParameterBagInterface   $parameter
    ) {
        $this->entityManager    = $entityManager;
        $this->parameter        = $parameter;

        parent::__construct();
    }
    /**
     * Run data initializing process.
     *
     * @return  void
     * @throws  RuntimeException            Process error.
     */
    protected function initializeData(): void
    {
        foreach ([
            Attribute::class,
            Category::class,
        ] as $entityClassName) {
            $this->entitiesBar[$entityClassName] = [];

            $allItems = $this
                ->entityManager
                ->getRepository($entityClassName)
                ->findAll();

            foreach ($allItems as $item) {
                switch ($entityClassName) {
                    case Attribute::class:
                        /** @var Attribute $item */
                        $index = "{$item->getTitle()}:{$item->getType()}";
                        break;
                    case Category::class:
                        /** @var Category $item */
                        $index = "{$item->getTitle()}:{$item->getCommodityType()}";
                        break;
                    default:
                        $index = $item->getId();
                }

                $this->entitiesBar[$entityClassName][$index] = $item;
            }
        }
    }
    /**
     * Find data provider file by "file" argument value.
     *
     * @param string $value "file" argument value.
     *
     * @return  SplFileInfo                 File.
     * @throws  RuntimeException            File is not valid.
     */
    protected function findDataProviderFile(string $value): SplFileInfo
    {
        $filePath   = $value[0] === DIRECTORY_SEPARATOR
            ? $value
            : $this->parameter->get('kernel.project_dir').DIRECTORY_SEPARATOR.$value;
        $file       = new SplFileInfo($filePath);

        if (!$file->isFile()) {
            throw new RuntimeException("{$file->getPathname()} is not a file");
        }

        if (!in_array($file->getExtension(), self::FILE_ALLOWED_EXTENSIONS)) {
            $allowedExtensions = implode(', ', self::FILE_ALLOWED_EXTENSIONS);
            throw new RuntimeException(
                'unsupported file extension, '.
                "allowed extensions are $allowedExtensions"
            );
        }

        return $file;
    }
    /**
     * Parse data provider file and extract its data.
     *
     * @param SplFileInfo $file File.
     *
     * @return  array[]                     File data.
     * @throws  RuntimeException            Any parse error.
     */
    protected function parseDataProviderFile(SplFileInfo $file): array
    {
        try {
            $fileExtension  = ucfirst(strtolower($file->getExtension()));
            $reader         = IOFactory::createReader($fileExtension);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet    = $reader->load($file->getPathname());
            $data           = [];

            foreach ($spreadsheet->getAllSheets() as $sheet) {
                $data[$sheet->getTitle()] = $sheet->toArray();
            }

            return $data;
        } catch (PhpSpreadsheetException $exception) {
            throw new RuntimeException(
                "data parsing failed with error: {$exception->getMessage()}"
            );
        }
    }
    /**
     * Normalize string "title" value.
     *
     * @param   string $value               Value.
     *
     * @return  string                      Value normalized.
     */
    protected function normalizeTitleValue(string $value): string
    {
        $encoding   = 'UTF-8';
        $value      = mb_ereg_replace('^[\ ]+', '', $value);
        $value      =
            mb_strtoupper(mb_substr($value, 0, 1, $encoding), $encoding).
            mb_substr($value, 1, mb_strlen($value), $encoding);

        return trim($value);
    }
    /**
     * Build attribute data.
     *
     * @param   string  $name               Attribute name.
     * @param   array   $values             Attribute values.
     *
     * @return  array                       Attribute data.
     * @throws  OutOfBoundsException        Not suitable attribute.
     */
    protected function buildAttributeData(string $name, array $values): array
    {
        $title      = $this->normalizeTitleValue($name);
        $isRequired = false;
        $type       = Attribute::TYPE_LIST;
        $dictionary = null;
        $listValues = $values;

        if ($title[strlen($title) - 1] === '*') {
            $title      = rtrim($title, '* ');
            $isRequired = true;
        }

        if (
            count($listValues) === 0 ||
            in_array(self::STRING_ATTRIBUTE_VALUE_MARK, $listValues)
        ) {
            $type       = Attribute::TYPE_STRING;
            $listValues = [];
        }

        if (in_array($title, self::NUMBER_ATTRIBUTES)) {
            $type       = Attribute::TYPE_INT;
            $listValues = [];
        }

        if (
            in_array(self::DICTIONARY_ATTRIBUTE_VALUE_MARK, $listValues) &&
            isset(self::WORKABLE_DICTIONARIES_MAP[$title])                      &&
            isset(Attribute::DICTIONARIES_DATA[self::WORKABLE_DICTIONARIES_MAP[$title]])
        ) {
            $type       = Attribute::TYPE_DICTIONARY;
            $dictionary = self::WORKABLE_DICTIONARIES_MAP[$title];
            $listValues = [];
        }

        return [
            'title'         => $title,
            'required'      => $isRequired,
            'type'          => $type,
            'dictionary'    => $dictionary,
            'listValues'    => array_unique(array_filter($listValues, function($value): bool {
                return is_string($value) && strlen($value) > 0;
            })),
        ];
    }
    /**
     * Get category, find exist or create new.
     *
     * @param   string  $name               Category name.
     * @param   string  $commodityType      Commodity type.
     *
     * @return  Category                    Category.
     */
    protected function getCategory(string $name, string $commodityType): Category
    {
        $index = "$name:$commodityType";

        if (isset($this->entitiesBar[Category::class][$index])) {
            return $this->entitiesBar[Category::class][$index];
        }

        $category = (new Category())
            ->setTitle($name)
            ->setCommodityType($commodityType);

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        $this->entitiesBar[Category::class][$index] = $category;
        return $category;
    }
    /**
     * Get attribute, find exist or create new.
     *
     * @param   string      $name           Attribute name.
     * @param   string      $type           Attribute type.
     * @param   string|null $dictionary     Attribute dictionary.
     *
     * @return  Attribute                   Attribute.
     */
    protected function getAttribute(
        string  $name,
        string  $type,
        ?string $dictionary = null
    ): Attribute {
        $index = "$name:$type";

        if (isset($this->entitiesBar[Attribute::class][$index])) {
            return $this->entitiesBar[Attribute::class][$index];
        }

        $attribute = (new Attribute())
            ->setTitle($name)
            ->setType($type)
            ->setDictionary($dictionary);

        $this->entityManager->persist($attribute);
        $this->entityManager->flush();

        $this->entitiesBar[Attribute::class][$index] = $attribute;
        return $attribute;
    }
}
