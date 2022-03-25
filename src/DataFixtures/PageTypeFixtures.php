<?php

namespace App\DataFixtures;

use App\Entity\Region;
use App\Entity\RegionTranslation;
use App\Entity\TypePage;
use App\Entity\TypePageTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PageTypeFixtures extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager)
    {
        $typePages = [
            [
                'code' => 'article',
                'uk' => 'Стаття',
                'en' => 'Article'
            ],
            [
                'code' => 'news',
                'uk' => 'Новини',
                'en' => 'News'
            ],
            [
                'code' => 'success_stories',
                'uk' => 'Історія успіху',
                'en' => 'History of success'
            ],
            [
                'code' => 'eco_articles',
                'uk' => 'Еко-статті',
                'en' => 'Eco-articles'
            ],

        ];
        foreach ($typePages as $item) {
            $reg = new TypePage();
            $regionEn = new TypePageTranslation();
            $regionEn->setName($item['en']);
            $regionEn->setLocale('en');
            $regionEn->setTranslatable($reg);
            $regionUk = new TypePageTranslation();
            $regionUk->setName($item['uk']);
            $regionUk->setLocale('uk');
            $regionUk->setTranslatable($reg);
            $reg->setCode($item['code']);
            $manager->persist($reg);
            $manager->persist($regionEn);
            $manager->persist($regionUk);
            $manager->flush();
        }
    }


    public static function getGroups(): array
    {
        return ['default','types'];
    }
}
