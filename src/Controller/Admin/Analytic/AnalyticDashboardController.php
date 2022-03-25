<?php

namespace App\Controller\Admin\Analytic;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnalyticDashboardController extends AbstractDashboardController
{
    private $urlGenerator;

    public function __construct(AdminUrlGenerator $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route("/admin/analytic", name="adminAnalytic", options={"i18n"=false})
     */
    public function index(): Response
    {
        $uri = $this->urlGenerator
            ->setRoute('adminAnalyticUser', ['show' => 'value'])
            ->set('menuIndex', 4)
            ->generateUrl();
        return $this->redirect($uri);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Аналітика');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Повернутися на сайт', 'fas fa-home', 'home');
        yield MenuItem::linkToUrl('Головна панель', 'fas fa-tachometer-alt', '/admin');
        yield MenuItem::linktoDashboard('Аналітика', 'fa fa-home');

        yield MenuItem::section('Користувачі');
        yield MenuItem::linkToRoute(
            'Аналітика по користувачам',
            'fas fa-user',
            'adminAnalyticUser',
            ['show' => 'value']);
        yield MenuItem::subMenu('Аналітика по продавцям', 'fas fa-user')->setSubItems([
            MenuItem::linkToRoute(
                'По формі реєстрації',
                'fas fa-user',
                'adminAnalyticUserSalers',
                ['type' => 'form', 'show' => 'value']
            ),
            MenuItem::linkToRoute(
                'По категоріям',
                'fas fa-user',
                'adminAnalyticUserSalers',
                ['type' => 'cats', 'show' => 'value']
            ),
            MenuItem::linkToRoute(
                'По регіонам',
                'fas fa-user',
                'adminAnalyticUserSalers',
                ['type' => 'regions', 'show' => 'value']
            ),
        ]);
        yield MenuItem::subMenu('Аналітика по постачальникам послуг', 'fas fa-user')->setSubItems([
            MenuItem::linkToRoute(
                'По формі реєстрації',
                'fas fa-user',
                'adminAnalyticUserServiceProviders',
                ['type' => 'form', 'show' => 'value']
            ),
            MenuItem::linkToRoute(
                'По категоріям',
                'fas fa-user',
                'adminAnalyticUserServiceProviders',
                ['type' => 'cats', 'show' => 'value']
            ),
            MenuItem::linkToRoute(
                'По регіонам',
                'fas fa-user',
                'adminAnalyticUserServiceProviders',
                ['type' => 'regions', 'show' => 'value']
            ),
        ]);

        yield MenuItem::section('Торгівельний майданчик');
        yield MenuItem::subMenu('Аналітика по товарам', 'fas fa-user')->setSubItems([
            MenuItem::linkToRoute(
                'Загальна',
                'fas fa-user',
                'adminAnalyticGoods',
                ['type' => 'all', 'show' => 'value']
            ),
            MenuItem::linkToRoute(
                'По формі регистрациї',
                'fas fa-user',
                'adminAnalyticGoods',
                ['type' => 'form', 'show' => 'value']
            ),
            MenuItem::linkToRoute(
                'По категоріям',
                'fas fa-user',
                'adminAnalyticGoods',
                ['type' => 'cats', 'show' => 'value']
            ),
            MenuItem::linkToRoute(
                'По регіонам',
                'fas fa-user',
                'adminAnalyticGoods',
                ['type' => 'regions', 'show' => 'value']
            ),
        ]);
        yield MenuItem::subMenu('Аналітика по послугам', 'fas fa-user')->setSubItems([
            MenuItem::linkToRoute(
                'По категоріям',
                'fas fa-user',
                'adminAnalyticServices',
                ['type' => 'cats', 'show' => 'value']
            ),
            MenuItem::linkToRoute(
                'По регіонам',
                'fas fa-user',
                'adminAnalyticServices',
                ['type' => 'regions', 'show' => 'value']
            ),
        ]);

    }
}
