<?php

namespace App\Controller\Admin;

use App\Entity\ItemRegistration;
use App\Service\ExportService;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Orm\EntityRepository;
use Symfony\Component\HttpFoundation\Request;

class OccurrenceRegistrationCrudController extends ItemRegistrationCrudController
{
    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return $this->get(EntityRepository::class)->createQueryBuilder($searchDto, $entityDto, $fields, $filters)->andWhere('entity.itemType = \'occurrence\'');
    }
    /**
     * @param ExportService $exportService
     *
     * @return void
     */
    public function export(ExportService $exportService,Request $request): void
    {
        $query = $this->getDoctrine()
            ->getRepository('App:ItemRegistration')
            ->createQueryBuilder('ir')->andWhere('ir.itemType = :item')
            ->setParameter('item','occurrence');

        $itemRegistrationList = $query->getQuery()->toIterable();
        $exportData = $this->getExportData($itemRegistrationList);


        $exportService->export('zahidRegistration', $exportData);
    }

    protected function getExportData(iterable $itemRegistrationList ):array
    {
        $exportData = [];

        /** @var ItemRegistration $itemRegistration */
        foreach ($itemRegistrationList as $itemRegistration) {
            $exportData[] = [
                'ID' => $itemRegistration->getId(),
                'Користувач' => $itemRegistration->getUserId()->getName(),
                'Регіон' => $itemRegistration->getUserId()->getRegion()
                    ? $itemRegistration->getUserId()->getRegion()->getName()
                    : null,
                'Захід' => $itemRegistration->getItemId()->getTitle(),
                'Отримав фидбек форму форму' =>  parent::findUserFeedback($itemRegistration)
                    ? $this->translator->trans('admin.itemRegitemistration.userFeedback.exist')
                    : $this->translator->trans('admin.itemRegistration.userFeedback.notExist'),
                'Дата' => $itemRegistration->getCreatedAt(),
            ];
        }

        return $exportData;
    }
}
