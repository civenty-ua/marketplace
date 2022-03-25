<?php

namespace App\Controller;

use App\Helper\SeoHelper;
use App\Service\SeoService;
use Symfony\{
    Bundle\FrameworkBundle\Controller\AbstractController,
    Component\Routing\Annotation\Route,
    Component\HttpFoundation\Request,
    Component\HttpFoundation\Response,
};
use App\Entity\{Category, Item, Options, Page, Partner, Tags};

/**
 * Class CalendarController
 * @package App\Controller
 */
class CalendarController extends AbstractController
{
    /**
     * @Route("/calendar", name="calendar")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function calendar(Request $request, SeoService $seoService): Response
    {
        //toDo Create command which sets isActive on items to false while startDate < now()
        $partners = $this->getDoctrine()->getRepository(Partner::class)->findPartnersIdsWithActiveItems();
        $topTags = $this->getDoctrine()->getRepository(Tags::class)->getTopAll($request->getLocale());
        $items = $this->getDoctrine()->getRepository(Item::class)->getItemsForCalendar();
        $categories = $this->getDoctrine()->getRepository(Category::class)->findActiveCategories();
        $pageList = $this->getDoctrine()->getRepository(Page::class)->findPageByTypeName('business_tools');

        $partnersIds = [];
        foreach ($items as $item) {
            $ids = '';
            foreach ($item->getPartners() as $partner) {
                $ids .= (string)$partner->getId() . ' ';
            }
            $partnersIds[$item->getId()] = $ids;
        }

        $seo = $seoService->setPage(SeoHelper::PAGE_CALENDAR)->getSeo();

        return $this->render('calendar/calendar.html.twig',
            [
                'seo' => $seo,
                'categories' => $categories,
                'partners' => $partners,
                'topTags' => $topTags,
                'items' => $items,
                'partnersIds' => $partnersIds,
                'pagesList' => $pageList,
            ]);
    }
}
