<?php
declare(strict_types=1);

namespace App\Admin\Field\Market;

use Doctrine\Persistence\ManagerRegistry;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\{
    AssociationField,
    FieldTrait,
};
use App\Entity\Market\Category;

use function count;
use function ksort;
/**
 * Market category field.
 */
class CategoryField implements FieldInterface
{
    use FieldTrait;
    /**
     * @inheritDoc
     */
    public static function new(
        string              $propertyName,
        ?string             $label          = null,
        ?string             $commodityType  = null,
        ?ManagerRegistry    $entityManager  = null
    ): FieldInterface {
        $field = AssociationField::new($propertyName, $label);

        if ($entityManager) {
            $field->setFormTypeOptions([
                'choices'       => self::getCategoriesTree($entityManager, $commodityType),
                'choice_label'  => function(?Category $category): string {
                    return $category ? self::makeCategoryTreeTitle($category) : '';
                },
            ]);
        }

        return $field;
    }
    /**
     * Get categories tree choices list.
     *
     * @param   ManagerRegistry $entityManager  Manager.
     * @param   string|null     $commodityType  Commodity type, if any.
     *
     * @return  array                           Categories tree.
     */
    public static function getCategoriesTree(
        ManagerRegistry $entityManager,
        ?string         $commodityType = null
    ): array {
        /** @var Category[] $categories */
        $filter     = $commodityType
            ? [
                'commodityType' => $commodityType,
            ]
            : [];
        $categories = $entityManager
            ->getRepository(Category::class)
            ->findBy($filter);

        return self::sortCategoriesList($categories);
    }
    /**
     * Build category title, like it is in a tree.
     *
     * @param   Category $category  Category.
     *
     * @return  string              Category title.
     */
    public static function makeCategoryTreeTitle(Category $category): string
    {
        $depthPrefix        = '';
        $currentCategory    = $category;

        while ($currentCategory->getParent() && $currentCategory->getParent() !== $currentCategory) {
            $depthPrefix       .= '. ';
            $currentCategory    = $currentCategory->getParent();
        }

        return "$depthPrefix{$category->getTitle()}";
    }
    /**
     * Sort categories list.
     *
     * @param   Category[] $categories  Categories.
     *
     * @return  Category[]              Categories sorted.
     */
    private static function sortCategoriesList(array $categories): array
    {
        $categoriesTree                 = [];
        $result                         = [];
        $processPlainCollectionToTree   = function(
            array      &$plainCollection,
            array      &$treeCollection,
            ?Category   $currentParent = null
        ) use (&$processPlainCollectionToTree): void {
            if (count($plainCollection) === 0) {
                return;
            }

            /** @var Category $category */
            foreach ($plainCollection as $index => $category) {
                if ($category->getParent() === $currentParent) {
                    $treeCollection[$category->getTitle()] = [
                        'category'  => $category,
                        'children'  => [],
                    ];

                    unset($plainCollection[$index]);
                    $processPlainCollectionToTree(
                        $plainCollection,
                        $treeCollection[$category->getTitle()]['children'],
                        $category
                    );
                }
            }

            ksort($treeCollection);
        };
        $processTreeCollectionToPlain   = function(
            array   $treeCollection,
            array   &$plainCollection
        ) use (&$processTreeCollectionToPlain): void {
            foreach ($treeCollection as $categoryData) {
                $plainCollection[] = $categoryData['category'];
                $processTreeCollectionToPlain($categoryData['children'], $plainCollection);
            }
        };

        $processPlainCollectionToTree($categories, $categoriesTree);
        $processTreeCollectionToPlain($categoriesTree, $result);

        return $result;
    }
}
