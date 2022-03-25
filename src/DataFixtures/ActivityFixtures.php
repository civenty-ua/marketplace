<?php

namespace App\DataFixtures;

use App\Entity\Activity;
use App\Entity\ActivityTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ActivityFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $activities = [
            ['uk' => 'Виробництво плодоовочів','en' => 'Production of fruits and vegetables'],
            ['uk' => 'Бджільництво','en' => 'Apiculture'],
            ['uk' => 'Закупівля с-г продукції','en' => 'Purchase of agricultural products'],
            ['uk' => 'Постачання засобів виробництва','en' => 'Supply of means of production'],
            ['uk' => 'Представляю ОТГ','en' => 'I represent OTG'],
            ['uk' => 'Навчання у ВНЗ','en' => 'Studying at the university'],
            ['uk' => 'Консультаційна діяльність','en' => 'Consulting activities'],
        ];

        foreach ($activities as $activity) {
            $entity = new Activity();
            $entityEn = new ActivityTranslation();
            $entityEn->setName($activity['en']);
            $entityEn->setLocale('en');
            $entityEn->setTranslatable($entity);
            $entityUk = new ActivityTranslation();
            $entityUk->setName($activity['uk']);
            $entityUk->setLocale('uk');
            $entityUk->setTranslatable($entity);
            $manager->persist($entity);
            $manager->persist($entityEn);
            $manager->persist($entityUk);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['default', 'activity'];
    }
}
