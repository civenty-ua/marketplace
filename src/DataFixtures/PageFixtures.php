<?php

namespace App\DataFixtures;

use App\Entity\Page;
use App\Entity\PageTranslation;
use App\Entity\TypePage;
use App\Entity\TypePageTranslation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PageFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $pathUK = __DIR__ . '/sources/action-project-page/uk.html';
        $fileUk = file_get_contents($pathUK);

        $pathEn = __DIR__ . '/sources/action-project-page/en.html';
        $fileEn = file_get_contents($pathEn);

        $businessCalcPathUK = __DIR__ . '/sources/action-project-page/business-tools/business-calc-uk.html';
        $businessCalcFileUK = file_get_contents($businessCalcPathUK);

        $businessCalcPathEN = __DIR__ . '/sources/action-project-page/business-tools/business-calc-en.html';
        $businessCalcFileEN = file_get_contents($businessCalcPathEN);

        $beeCalcPathUK = __DIR__ . '/sources/action-project-page/business-tools/bee-calc-uk.html';
        $beeCalcFileUK = file_get_contents($beeCalcPathUK);

        $beeCalcPathEN = __DIR__ . '/sources/action-project-page/business-tools/bee-calc-en.html';
        $beeCalcFileEN = file_get_contents($beeCalcPathEN);

        $businessWheelPathUK = __DIR__ . '/sources/action-project-page/business-tools/business-wheel-uk.html';
        $businessWheelFileUK = file_get_contents($businessWheelPathUK);

        $businessWheelPathEN = __DIR__ . '/sources/action-project-page/business-tools/business-wheel-en.html';
        $businessWheelFileEN = file_get_contents($businessWheelPathEN);

        $businessCanvasPathUK = __DIR__ . '/sources/action-project-page/business-tools/business-canvas-uk.html';
        $businessCanvasFileUK = file_get_contents($businessCanvasPathUK);

        $businessCanvasPathEN = __DIR__ . '/sources/action-project-page/business-tools/business-canvas-en.html';
        $businessCanvasFileEN = file_get_contents($businessCanvasPathEN);

        $beeCalendarPathUK = __DIR__ . '/sources/action-project-page/business-tools/bee-calendar-ua.html';
        $beeCalendarFileUK = file_get_contents($beeCalendarPathUK);

        $beeCalendarPathEN = __DIR__ . '/sources/action-project-page/business-tools/bee-calendar-en.html';
        $beeCalendarFileEN = file_get_contents($beeCalendarPathEN);

        $obsCalcPathUK = __DIR__ . '/sources/action-project-page/business-tools/obs-calc-uk.html';
        $obsCalcFileUK = file_get_contents($obsCalcPathUK);

        $obsCalcPathEN = __DIR__ . '/sources/action-project-page/business-tools/obs-calc-en.html';
        $obsCalcFileEN = file_get_contents($obsCalcPathEN);

        $mineralCalcPathUK = __DIR__ . '/sources/action-project-page/business-tools/mineral-calc-uk.html';
        $mineralCalcFileUK = file_get_contents($mineralCalcPathUK);

        $mineralCalcPathEN = __DIR__ . '/sources/action-project-page/business-tools/mineral-calc-en.html';
        $mineralCalcFileEN = file_get_contents($mineralCalcPathEN);

        $aboutUsPathUk = __DIR__ . '/sources/action-project-page/business-tools/about-us-uk.html';
        $aboutUsFileUk = file_get_contents($aboutUsPathUk);

        $typePages = [
            [
                'alias' => 'project-activities',
                'image_name' => '',
                'uk' => [

                    'title' => 'Діяльність проекту',
                    'content' => $fileUk,
                    'short' => '',
                ],
                'en' => [
                    'title' => 'Project activities',
                    'content' => $fileEn,
                    'short' => '',
                ],
            ],
            [
                'alias' => 'business-calculator',
                'image_name' => '1.png',
                'uk' => [
                    'title' => 'Бізнес калькулятор',
                    'content' => $businessCalcFileUK,
                    'short' => 'Яка собівартість меду та інших продуктів бджільництва? Яка рентабельність медового
                    бізнесу? Коли планувати покупку нової медогонки? Скільки коштів потрібно акумулювати, щоб розвивати
                    виробництво? На всі ці та багато інших питань відповість калькулятор, бджоляру потрібно тільки
                    внести у програму всі дані про витрати і доходи.',
                ],
                'en' => [
                    'title' => 'Business calculator',
                    'content' => $businessCalcFileEN,
                    'short' => 'What is the cost of honey and other bee products? What is the profitability of honey
                     business? When to plan to buy a new honey extractor? How much money do you need to accumulate to develop
                     production? All these and many other questions will be answered by a calculator, the beekeeper only needs
                     enter in the program all the data on costs and revenues.',
                ],
            ],
            [
                'alias' => 'business-calculator-for-beekeeper',
                'image_name' => 'business-calculator.png',
                'uk' => [
                    'title' => 'Бізнес калькулятор для бджоляра',
                    'content' => $beeCalcFileUK,
                    'short' => 'Яка собівартість меду та інших продуктів бджільництва? Яка рентабельність медового
                    бізнесу? Коли планувати покупку нової медогонки? Скільки коштів потрібно акумулювати, щоб розвивати
                    виробництво? На всі ці та багато інших питань відповість калькулятор, бджоляру потрібно тільки
                    внести у програму всі дані про витрати і доходи.',
                ],
                'en' => [
                    'title' => 'Business calculator for beekeeper',
                    'content' => $beeCalcFileEN,
                    'short' => 'What is the cost of honey and other bee products? What is the profitability of honey
                     business? When to plan to buy a new honey extractor? How much money do you need to accumulate to develop
                     production? All these and many other questions will be answered by a calculator, the beekeeper only needs
                     enter in the program all the data on costs and revenues.',
                ],
            ]
            ,
            [
                'alias' => 'business-wheel',
                'image_name' => '2.png',
                'uk' => [
                    'title' => 'Бізнес-колесо',
                    'content' => $businessWheelFileUK,
                    'short' => 'Яка собівартість меду та інших продуктів бджільництва? Яка рентабельність медового
                    бізнесу? Коли планувати покупку нової медогонки? Скільки коштів потрібно акумулювати, щоб розвивати
                    виробництво? На всі ці та багато інших питань відповість калькулятор, бджоляру потрібно тільки
                    внести у програму всі дані про витрати і доходи.',
                ],
                'en' => [
                    'title' => 'Business wheel',
                    'content' => $businessWheelFileEN,
                    'short' => 'What is the cost of honey and other bee products? What is the profitability of honey
                     business? When to plan to buy a new honey extractor? How much money do you need to accumulate to develop
                     production? All these and many other questions will be answered by a calculator, the beekeeper only needs
                     enter in the program all the data on costs and revenues.',
                ],
            ],
            [
                'alias' => 'business-canvas',
                'image_name' => '3.png',
                'uk' => [
                    'title' => 'Бізнес модель КАНВАС',
                    'content' => $businessCanvasFileUK,
                    'short' => 'Яка собівартість меду та інших продуктів бджільництва? Яка рентабельність медового
                    бізнесу? Коли планувати покупку нової медогонки? Скільки коштів потрібно акумулювати, щоб розвивати
                    виробництво? На всі ці та багато інших питань відповість калькулятор, бджоляру потрібно тільки
                    внести у програму всі дані про витрати і доходи.',
                ],
                'en' => [
                    'title' => 'Business model CANVAS',
                    'content' => $businessCanvasFileEN,
                    'short' => 'What is the cost of honey and other bee products? What is the profitability of honey
                     business? When to plan to buy a new honey extractor? How much money do you need to accumulate to develop
                     production? All these and many other questions will be answered by a calculator, the beekeeper only needs
                     enter in the program all the data on costs and revenues.',
                ],
            ],
            [
                'alias' => 'beekeeper-calendar',
                'image_name' => '4.png',
                'uk' => [
                    'title' => 'Календар для бджоляра',
                    'content' => $beeCalendarFileUK,
                    'short' => 'Яка собівартість меду та інших продуктів бджільництва? Яка рентабельність медового
                    бізнесу? Коли планувати покупку нової медогонки? Скільки коштів потрібно акумулювати, щоб розвивати
                    виробництво? На всі ці та багато інших питань відповість калькулятор, бджоляру потрібно тільки
                    внести у програму всі дані про витрати і доходи.',
                ],
                'en' => [
                    'title' => 'Beekeeper calendar',
                    'content' => $beeCalendarFileEN,
                    'short' => 'What is the cost of honey and other bee products? What is the profitability of honey
                     business? When to plan to buy a new honey extractor? How much money do you need to accumulate to develop
                     production? All these and many other questions will be answered by a calculator, the beekeeper only needs
                     enter in the program all the data on costs and revenues.',
                ],
            ],
            [
                'alias' => 'obs-calculator',
                'image_name' => '5.png',
                'uk' => [
                    'title' => 'OBS калькулятор',
                    'content' => $obsCalcFileUK,
                    'short' => 'Яка собівартість меду та інших продуктів бджільництва? Яка рентабельність медового
                    бізнесу? Коли планувати покупку нової медогонки? Скільки коштів потрібно акумулювати, щоб розвивати
                    виробництво? На всі ці та багато інших питань відповість калькулятор, бджоляру потрібно тільки
                    внести у програму всі дані про витрати і доходи.',
                ],
                'en' => [
                    'title' => 'OBS calculator',
                    'content' => $obsCalcFileEN,
                    'short' => 'What is the cost of honey and other bee products? What is the profitability of honey
                     business? When to plan to buy a new honey extractor? How much money do you need to accumulate to develop
                     production? All these and many other questions will be answered by a calculator, the beekeeper only needs
                     enter in the program all the data on costs and revenues.',
                ],
            ],
            [
                'alias' => 'calculator-of-mineral-fertilizers',
                'image_name' => '6.png',
                'uk' => [
                    'title' => 'Калькулятор мінеральних добрив',
                    'content' => $mineralCalcFileUK,
                    'short' => 'Яка собівартість меду та інших продуктів бджільництва? Яка рентабельність медового
                    бізнесу? Коли планувати покупку нової медогонки? Скільки коштів потрібно акумулювати, щоб розвивати
                    виробництво? На всі ці та багато інших питань відповість калькулятор, бджоляру потрібно тільки
                    внести у програму всі дані про витрати і доходи.',
                ],
                'en' => [
                    'title' => 'Calculator of mineral fertilizers',
                    'content' => $mineralCalcFileEN,
                    'short' => 'What is the cost of honey and other bee products? What is the profitability of honey
                     business? When to plan to buy a new honey extractor? How much money do you need to accumulate to develop
                     production? All these and many other questions will be answered by a calculator, the beekeeper only needs
                     enter in the program all the data on costs and revenues.',
                ],
            ],
            [
                'alias' => 'farm-box-platform',
                'image_name' => '7.png',
                'uk' => [
                    'title' => 'Платформа "Фермерська скринька"',
                    'content' => '',
                    'short' => 'Яка собівартість меду та інших продуктів бджільництва? Яка рентабельність медового
                    бізнесу? Коли планувати покупку нової медогонки? Скільки коштів потрібно акумулювати, щоб розвивати
                    виробництво? На всі ці та багато інших питань відповість калькулятор, бджоляру потрібно тільки
                    внести у програму всі дані про витрати і доходи.',
                ],
                'en' => [
                    'title' => 'Farm box platform',
                    'content' => '',
                    'short' => 'What is the cost of honey and other bee products? What is the profitability of honey
                     business? When to plan to buy a new honey extractor? How much money do you need to accumulate to develop
                     production? All these and many other questions will be answered by a calculator, the beekeeper only needs
                     enter in the program all the data on costs and revenues.',
                ],
            ],
            [
                'alias' => 'about-us',
                'image_name' => '',
                'uk' => [

                    'title' => 'Про нас',
                    'content' => $aboutUsFileUk,
                    'short' => '',
                ],
                'en' => [
                    'title' => 'About us',
                    'content' => '',
                    'short' => '',
                ],
            ],
        ];
        $businessTypeId = $manager->getRepository(TypePage::class)->findOneBy(['code' => 'business_tools']);
        if (!$businessTypeId) {
            $arr = [
                'code' => 'business_tools',
                'uk' => 'Бізнес-інструменти',
                'en' => 'Business tools',
            ];
            $reg = new TypePage();
            $regionEn = new TypePageTranslation();
            $regionEn->setName($arr['en']);
            $regionEn->setLocale('en');
            $regionEn->setTranslatable($reg);
            $regionUk = new TypePageTranslation();
            $regionUk->setName($arr['uk']);
            $regionUk->setLocale('uk');
            $regionUk->setTranslatable($reg);
            $reg->setCode($arr['code']);
            $manager->persist($reg);
            $manager->persist($regionEn);
            $manager->persist($regionUk);
            $manager->flush();
            $businessTypeId = $manager->getRepository(TypePage::class)->findOneBy(['code' => 'business_tools']);
        }
        foreach ($typePages as $item) {
            $object = new Page();

            if ($item['alias'] !== 'project-activities'
                && $item['alias'] !== 'marketplace'
                && $item['alias'] !== 'about-us'
            ) {
                $object->setTypePage($businessTypeId);
            }
            $object->setAlias($item['alias']);
            $object->setImageName($item['image_name']);
            $transEn = new PageTranslation();
            $transEn->setTitle($item['en']['title']);
            $transEn->setContent($item['en']['content']);
            $transEn->setLocale('en');
            $transEn->setShort($item['en']['short']);
            $transEn->setTranslatable($object);
            $transUk = new PageTranslation();
            $transUk->setTitle($item['uk']['title']);
            $transUk->setContent($item['uk']['content']);
            $transUk->setLocale('uk');
            $transUk->setShort($item['uk']['short']);
            $transUk->setTranslatable($object);
            $manager->persist($object);
            $manager->persist($transEn);
            $manager->persist($transUk);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['default', 'pages'];
    }
}
