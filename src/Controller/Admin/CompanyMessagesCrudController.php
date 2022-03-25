<?php

namespace App\Controller\Admin;

use App\Entity\CompanyMessages;
use App\Service\ExportService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\HttpFoundation\Response;

class CompanyMessagesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompanyMessages::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX,
                Action::new('companyMessagesExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('companyMessagesExport')
                    ->addCssClass('btn btn-primary'))
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->update(Crud::PAGE_INDEX,
                Action::DETAIL,
                function (Action $action) {
                    return $action
                        ->setIcon('fa fa-eye')
                        ->setLabel(false)
                        ->setHtmlAttributes([
                            'placeholder' => 'Показати',
                        ]);
                })
            ->remove(Crud::PAGE_DETAIL, Action::DELETE);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('admin.dashboard.messages_from_visitors.list_title')
            ->setEntityLabelInSingular('admin.dashboard.messages_from_visitors.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.messages_from_visitors.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.messages_from_visitors.edit_page_title')
            ->showEntityActionsInlined();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('phone')
            ->add('email')
            ->add('message');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->setLabel('ID');
        yield TextField::new('name')->setLabel('Ім\'я та прізвище');
        yield TextField::new('email')->setLabel('Email');
        yield TextField::new('phone')->setLabel('Телефон');
        yield TextField::new('message')->setLabel('Повідомлення');
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function companyMessagesExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:CompanyMessages')
            ->createQueryBuilder('cm')
            ->setMaxResults(1000)
            ->getQuery();

        $companyMessageList = $query->getResult();

        $exportData = [];

        /** @var CompanyMessages $companyMessage */
        foreach ($companyMessageList as $companyMessage) {

            $exportData[] = [
                'ID' => $companyMessage->getId(),
                "Ім'я та прізвище" => $companyMessage->getName(),
                'Телефон' => $companyMessage->getPhone(),
                'Повідомлення' => $companyMessage->getMessage(),
            ];
        }

        $exportService->export('companyMessage', $exportData);
    }
}