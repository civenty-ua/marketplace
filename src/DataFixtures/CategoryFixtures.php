<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\CategoryTranslation;
use App\Service\FileManager\FileManagerInterface;
use App\Service\FileManager\Mapping\CategoryBannerMapping;
use App\Service\FileManager\Mapping\CategoryImageMapping;
use Cocur\Slugify\SlugifyInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture implements FixtureGroupInterface
{
    private SlugifyInterface $slugify;
    private FileManagerInterface $fileManager;

    public function __construct(
        SlugifyInterface $slugify,
        FileManagerInterface $fileManager
    ) {
        $this->slugify = $slugify;
        $this->fileManager = $fileManager;
    }

    public function load(ObjectManager $manager)
    {
        $entitiesTranslations = $manager->getRepository(CategoryTranslation::class)->findAll();
        foreach ($entitiesTranslations as $entityTranslation) {
            $manager->remove($entityTranslation);
        }

        $entities = $manager->getRepository(Category::class)->findAll();
        foreach ($entities as $entity) {
            $manager->remove($entity);
        }
        $manager->flush();

        $categories = $this->getData();

        foreach ($categories as $item) {
            // TODO: add images in folder /sources/category/images/
            $item['banner'] = 'b1.jpg';

            $bannerName = $this->fileManager->uploadMappedFileByPath(
                __DIR__ . '/sources/category/images/' . $item['banner'],
                CategoryBannerMapping::class
            );

            $imageName = $this->fileManager->uploadMappedFileByPath(
                __DIR__ . '/sources/category/images/' . $item['image'],
                CategoryImageMapping::class
            );

            $reg = new Category();
            $categoryEn = new CategoryTranslation();
            if (!empty($item['en']['content'])) {
                $contentPath = __DIR__ . '/sources/category/content/' . $item['en']['content'] . '_en.html';

                if (file_exists($contentPath)) {
                    $content = file_get_contents($contentPath);
                    $categoryEn->setContent($content);
                }
            }
            $categoryEn->setName($item['en']['name']);
            $categoryEn->setLocale('en');
            $categoryEn->setTranslatable($reg);
            $categoryUk = new CategoryTranslation();
            if (!empty($item['uk']['content'])) {
                $contentPath = __DIR__ . '/sources/category/content/' . $item['uk']['content'] . '_uk.html';

                if (file_exists($contentPath)) {
                    $content = file_get_contents($contentPath);
                    $categoryUk->setContent($content);
                }
            }
            $categoryUk->setName($item['uk']['name']);
            $categoryUk->setLocale('uk');
            $categoryUk->setTranslatable($reg);
            $reg->setImage($imageName);
            $reg->setBanner($bannerName);
            $reg->setViewHomePage(true);
            $reg->setSort($item['sort']);
            $reg->setSlug($this->slugify->slugify($item['uk']['name']));
            $reg->setActive(true);
            $manager->persist($reg);
            $manager->persist($categoryEn);
            $manager->persist($categoryUk);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['default', 'category'];
    }

    private function getData()
    {
        return [
            [
                'banner' => 'b1.png',
                'image' => '1.png',
                'sort' => '10',
                'uk' => [
                    'name' => 'Відкритий грунт',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Open ground',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b2.png',
                'image' => '2.png',
                'sort' => '20',
                'uk' => [
                    'name' => 'Бджільництво',
                    'keywords' => '',
                    'description' => '',
                    'content' => '2',
                ],
                'en' => [
                    'name' => 'Apiculture',
                    'keywords' => '',
                    'description' => '',
                    'content' => '2',
                ]
            ],
            [
                'banner' => 'b3.png',
                'image' => '3.png',
                'sort' => '30',
                'uk' => [
                    'name' => 'Виноградарство',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Viticulture',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b4.png',
                'image' => '4.png',
                'sort' => '40',
                'uk' => [
                    'name' => "Трав'яний бізнес",
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Herbal business',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b5.png',
                'image' => '5.png',
                'sort' => '50',
                'uk' => [
                    'name' => 'Фрукти та ягоди',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Fruits and berries',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b6.png',
                'image' => '6.png',
                'sort' => '60',
                'uk' => [
                    'name' => 'Тепличний бізнес',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Greenhouse business',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b7.png',
                'image' => '7.png',
                'sort' => '70',
                'uk' => [
                    'name' => 'Грибівництво',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Mushroom growing',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b8.png',
                'image' => '8.png',
                'sort' => '80',
                'uk' => [
                    'name' => 'Ринок землі',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Land market',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b9.png',
                'image' => '9.png',
                'sort' => '90',
                'uk' => [
                    'name' => 'Сертифікація',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Certification',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b10.png',
                'image' => '10.png',
                'sort' => '100',
                'uk' => [
                    'name' => 'Жінки в агро',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Women in agro',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b11.png',
                'image' => '11.png',
                'sort' => '110',
                'uk' => [
                    'name' => 'Дослідження ринку',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Market research',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b12.png',
                'image' => '12.png',
                'sort' => '120',
                'uk' => [
                    'name' => 'Агропартнерства',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Agricultural partnerships',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ],
            [
                'banner' => 'b13.png',
                'image' => '13.png',
                'sort' => '130',
                'uk' => [
                    'name' => 'Фінансові ресурси',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ],
                'en' => [
                    'name' => 'Financial resources',
                    'keywords' => '',
                    'description' => '',
                    'content' => '',
                ]
            ]
        ];
    }
}
