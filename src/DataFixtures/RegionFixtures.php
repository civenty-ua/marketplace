<?php

namespace App\DataFixtures;

use App\Entity\Region;
use App\Entity\RegionTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class RegionFixtures extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager)
    {
        $regions = [
            ['uk' => 'АР Крим','en' => 'Autonomous Republic of Crimea'],
            ['uk' => 'Черкаська','en' => 'Cherkasy'],
            ['uk' => 'Чернігівська','en' => 'Chernihiv'],
            ['uk' => 'Чернівецька','en' => 'Chernivtsi'],
            ['uk' => 'Дніпропетровська','en' => 'Dnipropetrovsk'],
            ['uk' => 'Донецька','en' => 'Donetsk'],
            ['uk' => 'Івано-Франківська','en' => 'Ivano-Frankivsk'],
            ['uk' => 'Харківська','en' => 'Kharkiv'],
            [
                'uk'    => 'Херсонська',
                'en'    => 'Kherson',
                'code'  => 'kherson',
            ],
            ['uk' => 'Хмельницька','en' => 'Khmelnytsk '],
            ['uk' => 'Кіровоградська','en' => 'Kirovohrad '],
            ['uk' => 'Київська','en' => 'Kyiv'],
            ['uk' => 'Луганська','en' => 'Luhansk'],
            ['uk' => 'Львівська','en' => 'Lviv'],
            [
                'uk'    => 'Миколаївська',
                'en'    => 'Mykolaiv',
                'code'  => 'mykolaiv',
            ],
            [
                'uk'    => 'Одеська',
                'en'    => 'Odesa',
                'code'  => 'odesa',
            ],
            ['uk' => 'Полтавська','en' => 'Poltava'],
            ['uk' => 'Рівненська','en' => 'Rivne'],
            ['uk' => 'Сумська','en' => 'Sumy'],
            ['uk' => 'Тернопільська','en' => 'Ternopil'],
            ['uk' => 'Вінницька','en' => 'Vinnytsia'],
            ['uk' => 'Волинська','en' => 'Volyn'],
            ['uk' => 'Закарпатська','en' => 'Zakarpattia'],
            [
                'uk'    => 'Запорізька',
                'en'    => 'Zaporizhzhia',
                'code'  => 'zapogizdja',
            ],
            ['uk' => 'Житомирська','en' => 'Zhytomyr'],
        ];
        foreach ($regions as $region) {
            $reg = new Region();
            $reg->setCode($region['code'] ?? null);
            $regionEn = new RegionTranslation();
            $regionEn->setName($region['en']);
            $regionEn->setLocale('en');
            $regionEn->setTranslatable($reg);
            $regionUk = new RegionTranslation();
            $regionUk->setName($region['uk']);
            $regionUk->setLocale('uk');
            $regionUk->setTranslatable($reg);
            $manager->persist($reg);
            $manager->persist($regionEn);
            $manager->persist($regionUk);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['default'];
    }
}
