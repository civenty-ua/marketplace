<?php

namespace App\Helper;

class SeoHelper
{
    const PAGE_HOME = 'home';
    const PAGE_PAGE = 'page';
    const PAGE_NEWS = 'news';
    const PAGE_PARTNERS = 'partners';
    const PAGE_PARTNER = 'partner';
    const PAGE_EXPERTS = 'experts';
    const PAGE_EXPERT = 'expert';
    const PAGE_CONTACTS = 'contacts';
    const PAGE_SUCCESS_STORIES = 'success_stories';
    const PAGE_CALENDAR = 'calendar';
    const PAGE_CATEGORY = 'category';
    const PAGE_COURSE = 'course';
    const PAGE_WEBINAR = 'webinar';
    const PAGE_ARTICLE = 'article';
    const PAGE_SUCCESS_STORY = 'success_story';
    const PAGE_COURSES_AND_WEBINARS = 'courses_and_webinars';
    const PAGE_ECO_ARTICLES = 'eco_articles';
    const PAGE_MARKETPLACE_PRODUCT = 'marketplace_product';
    const PAGE_MARKETPLACE_PRODUCTS = 'marketplace_products';
    const PAGE_MARKETPLACE_SERVICE = 'marketplace_service';
    const PAGE_MARKETPLACE_SERVICES = 'marketplace_services';
    const PAGE_MARKETPLACE_KIT = 'marketplace_kit';
    const PAGE_MARKETPLACE_KITS = 'marketplace_kits';

    static public function formatLastModified($dateTime = null): string
    {
        if (is_string($dateTime)) {
            $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $dateTime);
        }

        if (!$dateTime instanceof \DateTimeInterface) {
            $dateTime = (new \DateTime())->modify("-3 days midnight");
        }

        return $dateTime->format('D, d M Y H:i:s') . ' GMT';
    }

    static public function setDefaultSeo($defaultSeo)
    {
        $seo = null;

        if (is_string($defaultSeo)) {
            $seo = [
                'meta_title' => $defaultSeo,
                'meta_description' => $defaultSeo,
                'meta_keywords' => $defaultSeo
            ];
        } elseif (is_array($defaultSeo)) {
            $seo = [
                'meta_title' => '',
                'meta_description' => '',
                'meta_keywords' => ''
            ];

            if (isset($defaultSeo['meta_title'])) {
                $seo['meta_title'] = $defaultSeo['meta_title'];
            }

            if (isset($defaultSeo['meta_description'])) {
                $seo['meta_description'] = $defaultSeo['meta_description'];
            }

            if (isset($defaultSeo['meta_keywords'])) {
                $seo['meta_keywords'] = $defaultSeo['meta_keywords'];
            }
        }

        return $seo;
    }


    static public function getOptionCodesByPage(string $page, string $language, array $data = []): array
    {
        $codes = [
            'meta_title' =>
                'meta_title_' . $page . '_' . $language,
            'meta_description' =>
                'meta_description_' . $page . '_' . $language,
            'meta_keywords' =>
                'meta_keywords_' . $page . '_' . $language,
        ];

        if ($page === self::PAGE_COURSES_AND_WEBINARS) {
            $type = $data['type'];

            $codes = [
                'meta_title' =>
                    'meta_title_' . $page . '_' . $type . '_' . $language,
                'meta_description' =>
                    'meta_description_' . $page . '_' . $type . '_' . $language,
                'meta_keywords' =>
                    'meta_keywords_' . $page . '_' . $type . '_' . $language,
            ];
        }

        return $codes;
    }

    static public function getSeoByCodeOptions(array $codes, array $options, array $variables = []): array
    {
        return [
            'meta_title' => self::getText($options[$codes['meta_title']], $variables),
            'meta_description' => self::getText($options[$codes['meta_description']], $variables),
            'meta_keywords' => self::getText($options[$codes['meta_keywords']], $variables)
        ];
    }

    static public function getText(string $text, array $variables = []): string
    {
        $pattern = '/{(.*?)}/';

        return preg_replace_callback($pattern, function ($match) use ($variables) {
            $key = $match[1];

            if (array_key_exists($key, $variables)) {
                return $variables[$key];
            }

            return '';
        }, $text);
    }
}
