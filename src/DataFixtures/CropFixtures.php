<?php

namespace App\DataFixtures;

use App\Entity\Crop;
use App\Entity\CropTranslation;
use App\Entity\Region;
use App\Entity\RegionTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class CropFixtures extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager)
    {
        $regions = [
            ['uk' => 'Виноград','en' => 'Grapes'],
            ['uk' => 'Огірок','en' => 'Cucumber'],
            ['uk' => 'Нішеві','en' => 'Niche'],
            ['uk' => 'Морква','en' => 'Carrots'],
            ['uk' => 'Цибуля','en' => 'Onion'],
            ['uk' => 'Капуста','en' => 'Cabbage'],
            ['uk' => 'Картопля','en' => 'Potato'],
            ['uk' => 'Помідор','en' => 'Tomato'],
            ['uk' => 'Горіх','en' => 'Nut'],
            ['uk' => 'Ягоди','en' => 'Berries'],
            ['uk' => 'Персик','en' => 'Peach'],
            ['uk' => 'Яблуня','en' => 'Apple'],
        ];
        foreach ($regions as $region) {
            $reg = new Crop();
            $regionEn = new CropTranslation();
            $regionEn->setName($region['en']);
            $regionEn->setLocale('en');
            $regionEn->setTranslatable($reg);
            $regionUk = new CropTranslation();
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
