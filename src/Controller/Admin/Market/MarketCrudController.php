<?php

namespace App\Controller\Admin\Market;

use EasyCorp\Bundle\EasyAdminBundle\Config\{
    Actions,
    Crud,
};
use App\Controller\Admin\BaseCrudController;
use App\Entity\User;
/**
 * Markets base CRUD controller.
 */
abstract class MarketCrudController extends BaseCrudController
{
    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions);
        foreach ($actions as $pageType) {
                $actions->setPermission($pageType,  User::ROLE_ADMIN_MARKET);
        }
        return $actions;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $messagesDomain = $this->getMessagesDomain();

        return $crud
            ->setPageTitle(Crud::PAGE_INDEX, "admin.market.$messagesDomain.titles.index")
            ->setPageTitle(Crud::PAGE_NEW, "admin.market.$messagesDomain.titles.new")
            ->setPageTitle(Crud::PAGE_EDIT, "admin.market.$messagesDomain.titles.edit")
            ->setEntityLabelInPlural("admin.market.$messagesDomain.titles.plural")
            ->setEntityLabelInSingular("admin.market.$messagesDomain.titles.singular")
            ->setFormThemes([
                '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                '@EasyAdmin/crud/form_theme.html.twig',
                'admin/market/form.html.twig',
            ]);
    }
    /**
     * Get messages domain.
     *
     * @return string                       Messages domain.
     */
    abstract protected function getMessagesDomain(): string;
}