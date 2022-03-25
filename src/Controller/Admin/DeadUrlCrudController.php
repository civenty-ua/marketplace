<?php

namespace App\Controller\Admin;

use App\Entity\DeadUrl;
use App\Service\ExportService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use function Matrix\add;

class DeadUrlCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return DeadUrl::class;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('deadRequest')
            ->add('redirectTo')
            ->add('isActive')
            ->add('createdAt')
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->setLabel('ID')->hideOnForm()
            ->setColumns('col-sm-6 col-md-4');
        yield DateField::new('createdAt')->setLabel('Дата створення')->setFormat('y-MM-dd H:m:s')->hideOnForm()
            ->setColumns('col-sm-6 col-md-4');
        yield TextareaField::new('deadRequest')->setLabel('Некорректний запит')
            ->setColumns('col-sm-6 col-md-4')->addCssClass('admin-word-break');
        yield TextareaField::new('redirectTo')->setLabel('Перенаправляти на')->hideOnIndex()
            ->setColumns('col-sm-6 col-md-4');
        yield IntegerField::new('attemptAmount')->onlyOnIndex()->setLabel('Кількість запитів')
            ->setColumns('col-sm-6 col-md-4');
        yield BooleanField::new('isActive')->setLabel('Включено')
            ->setColumns('col-sm-6 col-md-4');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)

            ->add(Crud::PAGE_INDEX,Action::DETAIL)
            ->add(Crud::PAGE_INDEX,
                Action::new('deadUrlExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('deadUrlExport')
                    ->addCssClass('btn btn-primary'))
            ->update(Crud::PAGE_INDEX,
                Action::EDIT,
                function (Action $action) {
                    return $action
                        ->setIcon('fas fa-edit')
                        ->setLabel(false)
                        ->setHtmlAttributes([
                            'placeholder' => 'Редагувати',
                        ]);
                })
            ->update(Crud::PAGE_INDEX,
                Action::DETAIL,
                function (Action $action) {
                    return $action
                        ->setIcon('fas fa-eye')
                        ->setLabel(false)
                        ->setHtmlAttributes([
                            'placeholder' => 'Переглянути',
                        ]);
                })
            ->update(Crud::PAGE_INDEX,
                Action::DELETE,
                function (Action $action) {
                    return $action
                        ->setIcon('fas fa-trash-alt')
                        ->setLabel(false)
                        ->setHtmlAttributes([
                            'placeholder' => 'Видалити',
                        ]);
                });

    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('admin.dashboard.dead_url.list_title')
            ->setEntityLabelInSingular('admin.dashboard.dead_url.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.dead_url.new_page_title')
            ->setPageTitle(Crud::PAGE_DETAIL, 'admin.dashboard.dead_url.edit_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.dead_url.edit_page_title')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->showEntityActionsInlined();
    }
    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function deadUrlExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:DeadUrl')
            ->createQueryBuilder('du')
            ->setMaxResults(1000)
            ->getQuery();

        $deadUrlList = $query->getResult();

        $exportData = [];

        /** @var DeadUrl $deadUrl */
        foreach ($deadUrlList as $deadUrl) {
            $exportData[] = [
                'ID' => $deadUrl->getId(),
                'Невірне посилання' => $deadUrl->getDeadRequest(),
                'Перенаправляты на ' => $deadUrl->getRedirectTo(),
                'Кількість запитів на неверне посилання' => $deadUrl->getAttemptAmount(),
                'Активна' => $deadUrl->getIsActive() ? 'Так' : 'Ні',
            ];
        }

        $exportService->export('deadUrl', $exportData);
    }

}