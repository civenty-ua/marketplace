<?php
declare(strict_types = 1);

namespace App\Command\Import\Market;

use Throwable;
use RuntimeException;
use OutOfBoundsException;
use SplFileInfo;
use Symfony\Component\Console\{
    Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Output\OutputInterface,
    Helper\ProgressBar,
};
use App\Entity\Market\{
    Attribute,
    Commodity,
    CategoryAttributeParameters,
    CategoryAttributeListValue,
};
/**
 * Import products categories command.
 */
class ImportProductsCategoriesCommand extends AbstractCategoriesImportCommand
{
    protected static $defaultName = 'app:import:market:categories-products';
    /**
     * @inheritDoc
     */
    protected function configure(): void
    {
        parent::configure();

        $this
            ->setDescription('Application data import: market products categories')
            ->setHelp('Run Application data import process for market products categories')
            ->addArgument(
                'file',
                InputArgument::REQUIRED,
                'path to source file, absolute or from application root '.
                '(for example: src/DataFixtures/sources/import/market/productsCategories/data.xlsx)'
            );
    }
    /**
     * @inheritDoc
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $outputData = [
            'success'   => 0,
            'failed'    => 0,
        ];

        try {
            $output->writeln('Required data initializing...');
            $this->initializeData();

            $output->writeln('File searching...');
            $file = $this->findDataProviderFile($input->getArgument('file'));

            $output->writeln('File reading...');
            $fileData = $this->parseDataProviderFile($file);
        } catch (RuntimeException $exception) {
            $output->writeln("ERROR: {$exception->getMessage()}");
            return Command::FAILURE;
        }

        $progressBar = new ProgressBar($output);
        $output->writeln('File data processing...');
        foreach ($progressBar->iterate($fileData) as $item) {
            try {
                $this->processItem($item);
                $outputData['success']++;
            } catch (Throwable $exception) {
                $outputData['failed']++;
                //TODO: make normal logging
                echo "ERROR: {$exception->getMessage()}\n";
            }
        }

        $output->writeln('File data processing finished');
        $output->writeln("Items processed: {$outputData['success']}");
        $output->writeln("Items failed: {$outputData['failed']}");

        return Command::SUCCESS;
    }
    /**
     * @inheritdoc
     */
    protected function parseDataProviderFile(SplFileInfo $file): array
    {
        $data   = parent::parseDataProviderFile($file);
        $result = [];

        foreach ($data as $category => $sheetData) {
            $subcategoriesData = $this->parseSheetDataWithSubcategories($sheetData);

            foreach ($subcategoriesData as $subCategory => $attributesData) {
                foreach ($attributesData as $index => $attributeData) {
                    try {
                        $attributesData[$index] = $this->buildAttributeData(
                            $attributeData['attribute'],
                            $attributeData['values']
                        );
                    } catch (OutOfBoundsException $exception) {
                        unset($attributesData[$index]);
                    }
                }

                $result[] = [
                    'category'      => $this->normalizeTitleValue($subCategory),
                    'parent'        => $this->normalizeTitleValue($category),
                    'attributes'    => $attributesData,
                ];
            }
        }

        return $result;
    }
    /**
     * Parse sheet data (subcategories data exist!).
     *
     * @param   array $sheetData            Sheet data.
     *
     * @return  array                       Parsed data.
     */
    private function parseSheetDataWithSubcategories(array $sheetData): array
    {
        $subCategory    = null;
        $result         = [];

        foreach ($sheetData as $row) {
            if (!empty($row[0])) {
                $subCategory            = $row[0];
                $result[$subCategory]   = [];

                foreach ($row as $cellIndex => $cell) {
                    if ($cellIndex !== 0 && !empty($cell)) {
                        $result[$subCategory][$cellIndex] = [
                            'attribute' => $cell,
                            'values'    => [],
                        ];
                    }
                }
            } elseif ($subCategory) {
                foreach ($row as $cellIndex => $cell) {
                    if (!empty($cell) && isset($result[$subCategory][$cellIndex])) {
                        $result[$subCategory][$cellIndex]['values'][] = $cell;
                    }
                }
            }
        }

        return $result;
    }
    /**
     * Process item data.
     *
     * @param   array $item                 Item data.
     *
     * @return  void
     */
    private function processItem(array $item): void
    {
        $category = $this->getCategory($item['category'], Commodity::TYPE_PRODUCT);

        if ($item['parent']) {
            $categoryParent = $this->getCategory($item['parent'], Commodity::TYPE_PRODUCT);
            $category->setParent($categoryParent);
        }

        foreach ($category->getCategoryAttributesParameters() as $attributesParameter) {
            $category->removeCategoryAttributeParameters($attributesParameter);
        }

        foreach ($item['attributes'] as $attributeData) {
            $attribute              = $this->getAttribute(
                $attributeData['title'],
                $attributeData['type'],
                $attributeData['dictionary']
            );
            $attributeParameters    = (new CategoryAttributeParameters())
                ->setCategory($category)
                ->setAttribute($attribute)
                ->setSort(10)
                ->setRequired($attributeData['required']);

            if ($attribute->getType() === Attribute::TYPE_LIST) {
                foreach ($attributeData['listValues'] as $listValue) {
                    $attributeListValue = (new CategoryAttributeListValue())
                        ->setCategoryAttribute($attributeParameters)
                        ->setValue($listValue);

                    $this->entityManager->persist($attributeListValue);
                    $attributeParameters->addCategoryAttributeListValue($attributeListValue);
                }
            }

            $this->entityManager->persist($attributeParameters);
            $category->addCategoryAttributeParameters($attributeParameters);
        }

        $this->entityManager->persist($category);
        $this->entityManager->flush();
    }
}
