<?php

namespace App\Controller\Admin;

use App\Entity\UserDownload;
use App\Service\ExportService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserDownloadCrudController extends BaseCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserDownload::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex()->setLabel('ID');
        yield DateTimeField::new('downloadedAt')->setLabel('admin.dashboard.user_download.downloaded_at')
            ->setFormat('y-MM-dd H:m:s');
        yield AssociationField::new('user')->setLabel('user_feedback.user.entity');
        yield TextField::new('title')->setLabel('feedback_form.question.title');
        yield TextField::new('filename')->setLabel('profile.downloaded_files')
        ->onlyOnDetail();
        yield TextField::new('ext')->setLabel('admin.dashboard.user_download.ext');
        yield TextField::new('link')->setLabel('admin.dashboard.dead_url.edit_button_title');
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setDefaultSort(['id' => 'DESC'])
            ->setSearchFields(['title','user.name','fileName'])
            ->setEntityLabelInPlural('admin.dashboard.user_download.list_title')
            ->setEntityLabelInSingular('admin.dashboard.user_download.edit_button_title')
            ->setPageTitle(Crud::PAGE_NEW, 'admin.dashboard.user_download.new_page_title')
            ->setPageTitle(Crud::PAGE_EDIT, 'admin.dashboard.user_download.edit_page_title')
            ->setFormThemes(
                [
                    '@A2lixTranslationForm/bootstrap_4_layout.html.twig',
                    '@FOSCKEditor/Form/ckeditor_widget.html.twig',
                    '@EasyAdmin/crud/form_theme.html.twig',
                ]
            )
            ->showEntityActionsInlined();
    }
    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::NEW)
            ->remove(Crud::PAGE_DETAIL, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX,Action::DETAIL)
            ->add(Crud::PAGE_INDEX,
                Action::new('userDownloadExport', 'Export')
                    ->createAsGlobalAction()
                    ->linkToCrudAction('userDownloadExport')
                    ->addCssClass('btn btn-primary'));
    }

    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function userDownloadExport(ExportService $exportService): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:UserDownload')
            ->createQueryBuilder('ud')
            ->setMaxResults(1000)
            ->getQuery();

        $userDownloadList = $query->getResult();

        $exportData = [];

        /** @var UserDownload $userDownload */
        foreach ($userDownloadList as $userDownload) {
            $exportData[] = [
                'ID' => $userDownload->getId(),
                'Ім\'я Користувача' => $userDownload->getUser(),
                'Назва ' => $userDownload->getTitle(),
                'Файл' => $userDownload->getFileName(),
                'Розширення Файла' => $userDownload->getExt(),
                'Посилання' => $userDownload->getLink(),
                'Дата скачування' => $userDownload->getDownloadedAt()
            ];
        }

        $exportService->export('userDownload', $exportData);
    }
}
