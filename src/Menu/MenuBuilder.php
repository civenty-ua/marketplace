<?php

namespace App\Menu;

use App\Entity\Category;
use App\Entity\Options;
use App\Entity\Page;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class MenuBuilder
 * @package App\Menu
 */
final class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private FactoryInterface $factory;

    /**
     * @var TranslatorInterface
     */
    protected TranslatorInterface $translator;

    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $em;

    /**
     * MenuBuilder constructor.
     *
     * @param FactoryInterface $factory
     * @param EntityManagerInterface $em
     * @param TranslatorInterface $translator
     */
    public function __construct(FactoryInterface $factory, EntityManagerInterface $em, TranslatorInterface $translator)
    {
        $this->factory = $factory;
        $this->em = $em;
        $this->translator = $translator;
    }

    /**
     * @param array $options
     *
     * @return ItemInterface
     */
    public function createMainMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('main');
        $menu->setChildrenAttribute('class', 'desktop-menu__inner-wrapper');

        // Study
        //

        $menu->addChild('study')
            ->setLabel($this->translator->trans('menu.study.label'))
            ->setAttribute('class', 'desktop-menu__item')
            ->setLabelAttribute('class', 'desktop-menu__title')
            ->setChildrenAttribute('class', 'desktop-menu__dropdown');
        $menu['study']->addChild($this->translator->trans('menu.study.courses'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'course']]);
        $menu['study']->addChild($this->translator->trans('menu.study.webinars'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'webinar']]);
        $menu['study']->addChild($this->translator->trans('menu.study.articles'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'article']]);
        $menu['study']->addChild($this->translator->trans('menu.study.other'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'other']]);
        $menu['study']->addChild($this->translator->trans('menu.study.occurrence'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'occurrence']]);
        $menu['study']->addChild($this->translator->trans('menu.about_us.news'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'news']]);
        $menu['study']->addChild($this->translator->trans('menu.about_us.success_stories'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'success_stories']]);

        // Trading floor
        //

        /** @var Options $enabledMarketPlace */
        $enabledMarketPlace = $this->em->getRepository(Options::class)->findOneBy(['code' => 'market_plase_enable' ]);
        $howItWorksPage = $this->em->getRepository(Page::class)->findOneBy(['alias' => 'yak-ce-pracyuye' ]);
        if (!is_null($enabledMarketPlace) and $enabledMarketPlace->getValue() == '1') {
            $menu->addChild('trading-floor')
                ->setLabel($this->translator->trans('menu.trading_floor.label'))
                ->setAttribute('class', 'desktop-menu__item beta')
                ->setLabelAttribute('class', 'desktop-menu__title')
                ->setChildrenAttribute('class', 'desktop-menu__dropdown');
            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.goods'), [
                'route' => 'products_list'
            ]);
            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.services'),
                ['route' => 'services_list']);
            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.proposals'),
                ['route' => 'kits_list']);

            if ($howItWorksPage) {
                $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.about'), [
                    'route' => 'custom_page',
                    'routeParameters' => ['alias' => $howItWorksPage->getAlias()]
                ]);
            }
        }

        // Calendar of events
        //

        $menu->addChild('calendar-of-events', ['route' => 'calendar'])
            ->setLabel($this->translator->trans('menu.calendar_of_events'))
            ->setAttribute('class', 'desktop-menu__item')
            ->setLinkAttribute('class', 'desktop-menu__title single');

        // Knowledge base
        //

        $menu->addChild('knowledge-base')
            ->setLabel($this->translator->trans('menu.knowledge_base'))
            ->setAttribute('class', 'desktop-menu__item')
            ->setLabelAttribute('class', 'desktop-menu__title')
            ->setChildrenAttribute('class', 'desktop-menu__dropdown desktop-menu__dropdown_grid');

        $menu['knowledge-base']->addChild($this->translator->trans('menu.about_us.eco-articles'),
            ['route' => 'eco_articles_list']);

        $categoryList = $this->em->getRepository(Category::class)->getCategoriesForMenuBuilder();

        /** @var Category $category */
        foreach ($categoryList as $category) {
            $menu['knowledge-base']->addChild($category->getName(),
                [
                    'route' => 'category_detail',
                    'routeParameters' => ['slug' => $category->getSlug()],
                ]);
        }

        // Business tools
        //

        $menu->addChild('business-tools')
            ->setLabel($this->translator->trans('menu.business_tools'))
            ->setAttribute('class', 'desktop-menu__item')
            ->setLabelAttribute('class', 'desktop-menu__title')
            ->setChildrenAttribute('class', 'desktop-menu__dropdown');

        $businessToolsList = $this->em->getRepository(Page::class)->findPageByTypeName('business_tools');

        /** @var Page $page */
        foreach ($businessToolsList as $page) {
            if ($page->getAlias() !== 'farm-box-platform') {
                $menu['business-tools']->addChild($page->getTitle(),
                    [
                        'route' => 'custom_page',
                        'routeParameters' => ['alias' => $page->getAlias()],
                    ]);
            } else {
                $menu['business-tools']->addChild($page->getTitle(),
                    [
                        'uri' => 'https://events.uhbdp.org/farmshop',
                    ]);
            }
        }


        // Catalog
        //
        $menu->addChild('catalog')
            ->setLabel($this->translator->trans('menu.catalog.label'))
            ->setAttribute('class', 'desktop-menu__item')
            ->setLabelAttribute('class', 'desktop-menu__title')
            ->setChildrenAttribute('class', 'desktop-menu__dropdown');
        $menu['catalog']->addChild($this->translator->trans('menu.catalog.catalog_of_ecotechnologies'),
            ['uri' => $this->options()['eco']]);
        $menu['catalog']->addChild($this->translator->trans('menu.catalog.catalog_of_small_mechanization'),
            ['uri' => $this->options()['mecha']]);
        $menu['catalog']->addChild($this->translator->trans('menu.catalog.support_program.code'),
            ['uri' => $this->options()['support']]);
        // About us

        $menu->addChild('about-us')
            ->setLabel($this->translator->trans('menu.about_us.label'))
            ->setAttribute('class', 'desktop-menu__item')
            ->setLabelAttribute('class', 'desktop-menu__title')
            ->setChildrenAttribute('class', 'desktop-menu__dropdown right');
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.project'), [
            'route' => 'custom_page',
            'routeParameters' => ['alias' => 'about-us'],
        ]);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.activity'),
            [
                'route' => 'custom_page',
                'routeParameters' => ['alias' => 'project-activities'],
            ]);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.partners'), ['route' => 'partners']);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.experts'),
            ['route' => 'experts']);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.contacts'),
            ['route' => 'contacts']);

        return $menu;
    }

    /**
     * @param array $options
     *
     * @return ItemInterface
     */
    public function createMobileMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('mobile');

        // Study
        //

        $menu->addChild('study')
            ->setLabel($this->translator->trans('menu.study.label'))
            ->setLabelAttribute('class',
                'js-accordion-block__open mobile-menu__item accordion-header accordion-header__title')
            ->setChildrenAttribute('class', 'js-accordion-block__content accordion-block__content');

        $menu['study']->addChild($this->translator->trans('menu.study.courses'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'course']]);
        $menu['study']->addChild($this->translator->trans('menu.study.webinars'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'webinar']]);
        $menu['study']->addChild($this->translator->trans('menu.study.articles'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'article']]);
        $menu['study']->addChild($this->translator->trans('menu.study.other'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'other']]);
        $menu['study']->addChild($this->translator->trans('menu.study.occurrence'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'occurrence']]);
        $menu['study']->addChild($this->translator->trans('menu.about_us.news'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'news']]);
        $menu['study']->addChild($this->translator->trans('menu.about_us.success_stories'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'success_stories']]);

        // Trading floor
        //

        /** @var Options $enabledMarketPlace */
        $enabledMarketPlace = $this->em->getRepository(Options::class)->findOneBy(['code' => 'market_plase_enable' ]);
        $howItWorksPage = $this->em->getRepository(Page::class)->findOneBy(['alias' => 'yak-ce-pracyuye' ]);
        if (!is_null($enabledMarketPlace) and $enabledMarketPlace->getValue() == '1') {
            $menu->addChild('trading-floor')
                ->setLabel($this->translator->trans('menu.trading_floor.label'))
                ->setLabelAttribute('class',
                    'js-accordion-block__open mobile-menu__item beta accordion-header accordion-header__title')
                ->setChildrenAttribute('class', 'js-accordion-block__content accordion-block__content');
            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.goods'), [
                'route' => 'products_list'
            ]);
            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.services'),
                ['route' => 'services_list']);
            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.proposals'),
                ['route' => 'kits_list']);

            if ($howItWorksPage) {
                $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.about'), [
                    'route' => 'custom_page',
                    'routeParameters' => ['alias' => $howItWorksPage->getAlias()]
                ]);
            }
        }

        // Calendar of events
        //

        $menu->addChild('calendar-of-events', ['route' => 'calendar'])
            ->setLabel($this->translator->trans('menu.calendar_of_events'))
            ->setLinkAttribute('class', 'mobile-menu__item accordion-header__title');

        // Knowledge base
        //

        $menu->addChild('knowledge-base')
            ->setLabel($this->translator->trans('menu.knowledge_base'))
            ->setLabelAttribute('class',
                'js-accordion-block__open mobile-menu__item accordion-header accordion-header__title')
            ->setChildrenAttribute('class', 'js-accordion-block__content accordion-block__content');

        $menu['knowledge-base']->addChild($this->translator->trans('menu.about_us.eco-articles'),
            ['route' => 'eco_articles_list']);

        $categoryList = $this->em->getRepository(Category::class)->findActiveCategories();

        /** @var Category $category */
        foreach ($categoryList as $category) {
            $name = $category->getName();
            if (is_null($name)) {
                $name = '';
            }
            $menu['knowledge-base']->addChild($name,
                [
                    'route' => 'category_detail',
                    'routeParameters' => ['slug' => $category->getSlug()],
                ]);
        }

        // Business tools
        //

        $menu->addChild('business-tools')
            ->setLabel($this->translator->trans('menu.business_tools'))
            ->setLabelAttribute('class',
                'js-accordion-block__open mobile-menu__item accordion-header accordion-header__title')
            ->setChildrenAttribute('class', 'js-accordion-block__content accordion-block__content');

        $businessToolsList = $this->em->getRepository(Page::class)->findPageByTypeName('business_tools');

        /** @var Page $page */
        foreach ($businessToolsList as $page) {
            if ($page->getAlias() !== 'farm-box-platform') {
                $menu['business-tools']->addChild($page->getTitle(),
                    [
                        'route' => 'custom_page',
                        'routeParameters' => ['alias' => $page->getAlias()],
                    ]);
            } else {
                $menu['business-tools']->addChild($page->getTitle(),
                    [
                        'uri' => 'https://events.uhbdp.org/farmshop',
                    ]);
            }
        }


        // Catalog
        //

        $menu->addChild('catalog')
            ->setLabel($this->translator->trans('menu.catalog.label'))
            ->setLabelAttribute('class',
                'js-accordion-block__open mobile-menu__item accordion-header accordion-header__title')
            ->setChildrenAttribute('class', 'js-accordion-block__content accordion-block__content');
        $menu['catalog']->addChild($this->translator->trans('menu.catalog.catalog_of_ecotechnologies'),
            ['uri' => $this->options()['eco']]);
        $menu['catalog']->addChild($this->translator->trans('menu.catalog.catalog_of_small_mechanization'),
            ['uri' => $this->options()['mecha']]);
        $menu['catalog']->addChild($this->translator->trans('menu.catalog.support_program.code'),
            ['uri' => $this->options()['support']]);

        // About us
        //

        $menu->addChild('about-us')
            ->setLabel($this->translator->trans('menu.about_us.label'))
            ->setLabelAttribute('class',
                'js-accordion-block__open mobile-menu__item accordion-header accordion-header__title')
            ->setChildrenAttribute('class', 'js-accordion-block__content accordion-block__content');
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.project'), [
            'route' => 'custom_page',
            'routeParameters' => ['alias' => 'about-us'],
        ]);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.activity'),
            [
                'route' => 'custom_page',
                'routeParameters' => ['alias' => 'project-activities'],
            ]);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.partners'), ['route' => 'partners']);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.experts'),
            ['route' => 'experts']);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.contacts'),
            ['route' => 'contacts']);

        return $menu;
    }

    /**
     * @param array $options
     *
     * @return ItemInterface
     */
    public function createFooterMenu(array $options): ItemInterface
    {
        $menu = $this->factory->createItem('footer');
        $menu->setChildrenAttribute('class', 'footer-category__content');

        // Study
        //

        $menu->addChild('study')
            ->setLabel($this->translator->trans('menu.study.label'))
            ->setAttribute('class', 'footer-category')
            ->setLabelAttribute('class', 'footer-category__title')
            ->setChildrenAttribute('class', 'footer-category__ul');
        $menu['study']->addChild($this->translator->trans('menu.study.courses'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'course']]);
        $menu['study']->addChild($this->translator->trans('menu.study.webinars'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'webinar']]);
        $menu['study']->addChild($this->translator->trans('menu.study.articles'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'article']]);
        $menu['study']->addChild($this->translator->trans('menu.study.other'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'other']]);
        $menu['study']->addChild($this->translator->trans('menu.study.occurrence'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'occurrence']]);
        $menu['study']->addChild($this->translator->trans('menu.calendar_of_events'), ['route' => 'calendar']);
        $menu['study']->addChild($this->translator->trans('menu.about_us.news'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'news']]);
        $menu['study']->addChild($this->translator->trans('menu.about_us.success_stories'),
            ['route' => 'courses_and_webinars', 'routeParameters' => ['type' => 'success_stories']]);


        // Calendar of events
        //


        // Knowledge base
        //

        $menu->addChild('knowledge-base')
            ->setLabel($this->translator->trans('menu.knowledge_base'))
            ->setAttribute('class', 'footer-category')
            ->setLabelAttribute('class', 'footer-category__title')
            ->setChildrenAttribute('class', 'footer-category__ul footer-category__ul_grid');

        $menu['knowledge-base']->addChild($this->translator->trans('menu.about_us.eco-articles'),
            ['route' => 'eco_articles_list']);

        $categoryList = $this->em->getRepository(Category::class)->getCategoriesForMenuBuilder();

        /** @var Category $category */
        foreach ($categoryList as $category) {
            $menu['knowledge-base']->addChild($category->getName(),
                [
                    'route' => 'category_detail',
                    'routeParameters' => ['slug' => $category->getSlug()],
                ]);
        }

        // Business tools
        //

        $menu->addChild('business-tools')
            ->setLabel($this->translator->trans('menu.business_tools'))
            ->setAttribute('class', 'footer-category')
            ->setLabelAttribute('class', 'footer-category__title')
            ->setChildrenAttribute('class', 'footer-category__ul two-columns');

        $businessToolsList = $this->em->getRepository(Page::class)->findPageByTypeName('business_tools');

        /** @var Page $page */
        foreach ($businessToolsList as $page) {
            if ($page->getAlias() !== 'farm-box-platform') {
                $menu['business-tools']->addChild($page->getTitle(),
                    [
                        'route' => 'custom_page',
                        'routeParameters' => ['alias' => $page->getAlias()],
                    ]);
            } else {
                $menu['business-tools']->addChild($page->getTitle(),
                    [
                        'uri' => 'https://events.uhbdp.org/farmshop',
                    ]);
            }
        }

        // Catalog
        //

        $menu->addChild('catalog')
            ->setLabel($this->translator->trans('menu.catalog.label'))
            ->setAttribute('class', 'footer-category')
            ->setLabelAttribute('class', 'footer-category__title')
            ->setChildrenAttribute('class', 'footer-category__ul');
        $menu['catalog']->addChild($this->translator->trans('menu.catalog.catalog_of_ecotechnologies'),
            ['uri' => $this->options()['eco']]);
        $menu['catalog']->addChild($this->translator->trans('menu.catalog.catalog_of_small_mechanization'),
            ['uri' => $this->options()['mecha']]);
        $menu['catalog']->addChild($this->translator->trans('menu.catalog.support_program.code'),
            ['uri' => $this->options()['support']]);

        // About us
        //

        $menu->addChild('about-us')
            ->setLabel($this->translator->trans('menu.about_us.label'))
            ->setAttribute('class', 'footer-category')
            ->setLabelAttribute('class', 'footer-category__title')
            ->setChildrenAttribute('class', 'footer-category__ul');
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.project'), [
            'route' => 'custom_page',
            'routeParameters' => ['alias' => 'about-us'],
        ]);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.activity'),
            [
                'route' => 'custom_page',
                'routeParameters' => ['alias' => 'project-activities'],
            ]);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.partners'), ['route' => 'partners']);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.experts'),
            ['route' => 'experts']);
        $menu['about-us']->addChild($this->translator->trans('menu.about_us.contacts'),
            ['route' => 'contacts']);

        // Trading floor
        //
        /** @var Options $enabledMarketPlace */
        $enabledMarketPlace = $this->em->getRepository(Options::class)->findOneBy(['code' => 'market_plase_enable' ]);
        $howItWorksPage = $this->em->getRepository(Page::class)->findOneBy(['alias' => 'yak-ce-pracyuye' ]);
        if (!is_null($enabledMarketPlace) and $enabledMarketPlace->getValue() == '1') {
            $menu->addChild('trading-floor')
                ->setLabel($this->translator->trans('menu.trading_floor.label'))
                ->setAttribute('class', 'footer-category')
                ->setLabelAttribute('class', 'footer-category__title')
                ->setChildrenAttribute('class', 'footer-category__ul');
            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.goods'), [
                'route' => 'products_list'
            ]);
            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.services'),
                ['route' => 'services_list']);
            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.proposals'),
                ['route' => 'kits_list']);
//            $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.proposals'),
//                ['route' => 'proposals']);

            if ($howItWorksPage) {
                $menu['trading-floor']->addChild($this->translator->trans('menu.trading_floor.about'), [
                    'route' => 'custom_page',
                    'routeParameters' => ['alias' => $howItWorksPage->getAlias()]
                ]);
            }
        }

        return $menu;
    }

    private function options(): array
    {
        $opt = $this->em->getRepository(Options::class)->findBy(['code' => 'catalog_eco']);
        $links['eco'] = (string)$opt[0]->getValue();
        $opt = $this->em->getRepository(Options::class)->findBy(['code' => 'catalog_mecha']);
        $links['mecha'] = (string)$opt[0]->getValue();
        $opt = $this->em->getRepository(Options::class)->findBy(['code' => 'menu_catalog_support_program']);
        $links['support'] = (string)$opt[0]->getValue();
        return $links;
    }
}
