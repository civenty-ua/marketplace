<?php

namespace App\Controller\Admin\Analytic;

use App\Entity\Market\Category;
use App\Entity\Market\Commodity;
use App\Entity\Market\CommodityKit;
use App\Entity\Market\CommodityProduct;
use App\Entity\Market\CommodityService;
use App\Entity\Region;
use App\Repository\Market\CommodityServiceRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnalyticCommodityController extends AnalyticBaseController
{
    /**
     * @Route("/admin/analytic/goods/{type}/{show}", name="adminAnalyticGoods", options={"i18n"=false})
     */
    public function GoodsAnalytic($type, $show): Response
    {
        $commodity = $this->getDoctrine()->getRepository(Commodity::class)
            ->createQueryBuilder('p');

        $productCom = $this->getDoctrine()->getRepository(CommodityProduct::class)
            ->createQueryBuilder('cp');

        switch ($type) {
            case 'all':
                $data['ALL'] = $commodity->select($commodity->expr()->count('p.id'))
                    ->getQuery()
                    ->getSingleScalarResult();

                $data['Продукти'] = $commodity->select($commodity->expr()->count('p.id'))
                    ->where($commodity->expr()->isInstanceOf('p', CommodityProduct::class))
                    ->andWhere($commodity->expr()->eq('p.isActive', 1))
                    ->getQuery()
                    ->getSingleScalarResult();

                $data['Послуги'] = $commodity->select($commodity->expr()->count('p.id'))
                    ->where($commodity->expr()->isInstanceOf('p', CommodityService::class))
                    ->andWhere($commodity->expr()->eq('p.isActive', 1))
                    ->getQuery()
                    ->getSingleScalarResult();

                $data['Пропозиції'] = $commodity->select($commodity->expr()->count('p.id'))
                    ->where($commodity->expr()->isInstanceOf('p', CommodityKit::class))
                    ->andWhere($commodity->expr()->eq('p.isActive', 1))
                    ->getQuery()
                    ->getSingleScalarResult();

                break;

            case 'form':
                /*  $itemsQuery = $this
                      ->getDoctrine()
                      ->getManager()
                      ->getRepository(CommodityProduct::class)
                      ->getTotalCount(
                          null,
                          [
                              'type'          => CommodityProduct::TYPE_BUY,
                          ]
                      );
                  dd($itemsQuery);*/
                $products = $commodity->select('p')
                    ->where($commodity->expr()->isInstanceOf('p', CommodityProduct::class))
                    ->andWhere($commodity->expr()->eq('p.isActive', 1))
                    ->getQuery()->getResult();
                $counter = 0;

                foreach ($products as $product)
                {
                    if($product->getType() == 'buy') $counter ++;
                }
                $data['Продаж'] = $counter;

                $counter = 0;
                foreach ($products as $product)
                {
                    if($product->getType() == 'sell') $counter ++;
                }
                $data['Купівля'] = $counter;
                break;

            case 'cats':
                $List = $this->getDoctrine()->getRepository(Category::class)
                    ->createQueryBuilder('c');
                $List = $List->where($List->expr()->eq('c.commodityType', "'product'"))
                    ->getQuery()
                    ->getResult();
                /** @var Category $List */
                foreach ($List as $category)
                {
                    $data[$category->getTitle()] = $productCom->select($productCom->expr()->count('cp.id'))
                        ->where($productCom->expr()->eq('cp.category', $category->getId()))
                        ->andWhere($productCom->expr()->eq('cp.isActive', 1))
                        ->getQuery()
                        ->getSingleScalarResult();
                }
                break;
            case 'regions':
                $List = $this->getDoctrine()->getRepository(Region::class)
                    ->createQueryBuilder('cr')
                    ->getQuery()
                    ->getResult();
                /** @var Region $List */
                foreach ($List as $category)
                {
                    $data[$category->translate()->getName()] = $productCom->select($productCom->expr()->count('cp.id'))
                        ->where($productCom->expr()->eq('cp.region', $category->getId()))
                        ->andWhere($productCom->expr()->eq('cp.isActive', 1))
                        ->getQuery()
                        ->getSingleScalarResult();
                }
                break;
        }

        return $this->render('admin/analytic/view.html.twig', [
            'analytic_page_title' => 'Аналітика по товарам(активні)',
            'data' => $data,
            'show' => $show,
        ]);
    }

    /**
     * @Route("/admin/analytic/services/{type}/{show}", name="adminAnalyticServices", options={"i18n"=false})
     */
    public function ServicesAnalytic($type, $show): Response
    {
        /**
         * @var CommodityServiceRepository $productCom
         */
        $productCom = $this->getDoctrine()->getRepository(CommodityService::class);
        switch ($type) {
            case 'cats':
                $List = $this->getDoctrine()->getRepository(Category::class)
                    ->createQueryBuilder('c');
                $Commodity = Commodity::TYPE_SERVICE;
                $List = $List->where($List->expr()->eq('c.commodityType', "'$Commodity'"))
                    ->getQuery()
                    ->getResult();
                /** @var Category $category */
                foreach ($List as $category)
                {
                    $data[$category->getTitle()] = $productCom->getTotalCount(null,[
                        'category' => $category->getId()
                    ]);
                }
                break;
            case 'regions':
                $List = $this->getDoctrine()->getRepository(Region::class)
                    ->createQueryBuilder('cr')
                    ->getQuery()
                    ->getResult();
                /** @var Region $region */
                foreach ($List as $region)
                {
                    $data[$region->translate()->getName()] = $productCom->getTotalCount(null,[
                        'userRegion' => $region->getId()
                    ]);
                }
                break;
        }

        return $this->render('admin/analytic/view.html.twig', [
            'analytic_page_title' => 'Аналітика по послугам(активні)',
            'data' => $data,
            'show' => $show,
        ]);
    }

}
