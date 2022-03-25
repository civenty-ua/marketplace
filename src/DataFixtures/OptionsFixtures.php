<?php

namespace App\DataFixtures;

use App\Entity\Options;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class OptionsFixtures extends Fixture implements FixtureGroupInterface
{

    public function load(ObjectManager $manager)
    {
        $options = [
            //HEADER
            ['code' => 'facebook', 'value' => 'https://www.facebook.com/uhbdp', 'description' => 'Посилання на групу в facebook', 'type' => Options::HEADER],
            ['code' => 'twitter', 'value' => 'https://twitter.com/UHBDP', 'description' => 'Посилання на  twitter', 'type' => Options::HEADER],
            ['code' => 'youtube', 'value' => 'https://www.youtube.com/channel/UCVCwYvmQVt0s8V2Z29fDOnA', 'description' => 'Посилання на  youtube', 'type' => Options::HEADER],
            ['code' => 'telegram', 'value' => 'https://t.me/uhbdp', 'description' => 'Посилання на  telegram', 'type' => Options::HEADER],
            ['code' => 'instagram', 'value' => 'https://www.instagram.com/uhbdp/', 'description' => 'Посилання на  instagram', 'type' => Options::HEADER],
            ['code' => 'email', 'value' => 'info@uhbdp.org', 'description' => 'Адреса електронної пошти', 'type' => Options::HEADER],
            ['code' => 'phone', 'value' => '0 800 500 184', 'description' => 'Телефон', 'type' => Options::HEADER],
            ['code' => 'news_banner_image', 'value' => '/images/news-banner.jpg', 'description' => 'Баннер сторінки Новини', 'type' => Options::HEADER],
            ['code' => 'news_banner_image_link', 'value' => 'https://uhbdp.org/ua/news/project-news', 'description' => 'Посилання баннера Новини', 'type' => Options::HEADER],
            ['code' => 'catalog_eco', 'value' => 'https://enviro.uhbdp.org/ua/', 'description' => 'Посилання на каталог еко-технологій', 'type' => Options::CATALOG],
            ['code' => 'catalog_mecha', 'value' => 'https://techno.uhbdp.org/ua/', 'description' => 'Посилання на каталог малої механізації', 'type' => Options::CATALOG],
            ['code' => 'GTM_head', 'value' => "
(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-NFFRVRQ');", 'description' => 'Google Tag Manager in head', 'type' => Options::HEADER],
            ['code' => 'GTM_body', 'value' => 'src="https://www.googletagmanager.com/ns.html?id=GTM-NFFRVRQ"
height="0" width="0" style="display:none;visibility:hidden"', 'description' => 'Google Tag Manager in body', 'type' => Options::HEADER],
            ['code' => 'canada', 'value' => 'https://www.international.gc.ca/', 'description' => 'Посилання на ресурс Канади', 'type' => Options::HEADER],
            ['code' => 'meda', 'value' => 'https://www.meda.org/', 'description' => 'Посилання на ресурс MEDA', 'type' => Options::HEADER],
            ['code' => 'farm_box_platform', 'value' => 'https://events.uhbdp.org/farmshop', 'description' => 'Посилання на ресурс Фермерська скринька', 'type' => Options::CONTENT],
            ['code' => 'default_keywords', 'value' => 'test keywords', 'description' => 'Ключові слова за умовленням', 'type' => Options::CONTENT],
            ['code' => 'default_description', 'value' => 'some description', 'description' => 'Опис за умовленням', 'type' => Options::CONTENT],
            ['code' => 'category_static_link', 'value' => 'https://www.meda.org', 'description' => 'Посилання на сторінках Бази Знань', 'type' => Options::CONTENT],
            [
                'code' => 'rules_agreement_link',
                'value' => '/rules_agreement',
                'description' => 'Посилання на сторінку узгодження з правилами сервісу',
                'type' => Options::CONTENT,
            ],
            [
                'code' => 'personal_data_process_agreement_link',
                'value' => '/personal_data_process_agreement',
                'description' => 'Посилання на сторінку дозволу обробки персональних данних',
                'type' => Options::CONTENT,
            ],
            //HOME_PAGE
            [
                'code' => 'homepage_footer_h1_uk',
                'value' => 'Чому вчитися з UHBDP',
                'description' => 'Заголовок блоку в футері на головній сторінці українскою',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_h1_en',
                'value' => 'Why learn from UHBDP',
                'description' => 'Заголовок блоку в футері на головній сторінці англійською',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_h2_uk',
                'value' => 'Метою UHBDP є свободна доступість знань для виробників садівництва',
                'description' => 'Підзаголовок блоку в футері на головній сторінці українскою ',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_h2_en',
                'value' => 'The goal of UHBDP is free access to knowledge for horticultural growers',
                'description' => 'Підзаголовок блоку в футері на головній сторінці англійською',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_left_block_header_uk',
                'value' => 'Задача організації',
                'description' => 'Заголовок лівого підблоку в футері на головній сторінці українскою',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_left_block_header_en',
                'value' => 'The task of the organization',
                'description' => 'Заголовок лівого підблоку в футері на головній сторінці англійською',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_left_block_content_uk',
                'value' => 'Таким образом, высококачественный прототип будущего проекта требует анализа переосмысления внешнеэкономических политик.',
                'description' => 'Контент лівого підблоку в футері на головній сторінці українскою',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_left_block_content_en',
                'value' => 'Thus, a high-quality prototype of a future project requires an analysis of the rethinking of foreign economic policies.',
                'description' => 'Контент лівого підблоку в футері на головній сторінці англійською',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_middle_block_header_uk',
                'value' => 'Сложно сказать, почему акционеры',
                'description' => 'Заголовок центрального підблоку в футері на головній сторінці українскою',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_middle_block_header_en',
                'value' => 'It\'s hard to say why shareholders',
                'description' => 'Заголовок центрального підблоку в футері на головній сторінці англійською',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_middle_block_content_uk',
                'value' => 'Вот вам яркий пример современных тенденций - сплочённость команды профессионалов, а также свежий взгляд на привычные вещи.',
                'description' => 'Контент центрального підблоку в футері на головній сторінці українскою',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_middle_block_content_en',
                'value' => 'Here is a vivid example of modern trends - the solidarity of a team of professionals, as well as a fresh look at familiar things.',
                'description' => 'Контент центрального підблоку в футері на головній сторінці англійською',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_right_block_header_uk',
                'value' => 'А также некоторые внутренней инициированные',
                'description' => 'Заголовок правого підблоку в футері на головній сторінці українскою',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_right_block_header_en',
                'value' => 'And also some inner initiated',
                'description' => 'Заголовок правого підблоку в футері на головній сторінці англійською',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_right_block_content_uk',
                'value' => 'Прежде всего, выбранный нами инновационный путь обеспечивает актуальность глубокомысленных рассуждений.',
                'description' => 'Контент центрального підблоку в футері на головній сторінці українскою',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'homepage_footer_right_block_content_en',
                'value' => 'First of all, the innovative path we have chosen ensures the relevance of thoughtful reasoning.',
                'description' => 'Контент центрального підблоку в футері на головній сторінці англійською',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'courses_and_webinars_page_title_en',
                'value' => 'You are in the heart of the portal. Find your knowledge',
                'description' => 'Заголовок сторінки курсів та вебінарів англійською',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'courses_and_webinars_page_title_uk',
                'value' => 'Ви у серці порталу. Знаходьте свої знання',
                'description' => 'Заголовок сторінки курсів та вебінарів українскою',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'count_user',
                'value' => '48196',
                'description' => 'Количество зарегистрированных пользователей в системе',
                'type' => Options::HOME_PAGE,
            ],
            [
                'code' => 'market_plase_enable',
                'value' => '0',
                'description' => 'Включить отображение меню для маркетплейса',
                'type' => Options::HEADER,
            ],
        ];

        foreach ($options as $item) {
            $option = new Options();

            $option->setCode($item['code'])
                ->setDescription($item['description'])
                ->setValue($item['value'])
                ->setType($item['type']);

            $manager->persist($option);
        }
        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['default', 'options'];
    }
}
