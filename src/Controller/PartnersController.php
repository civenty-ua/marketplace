<?php

namespace App\Controller;

use App\Entity\Tags;
use App\Helper\SeoHelper;
use App\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\{JsonResponse, Request, Response};
use Symfony\Component\Form\Exception\RuntimeException as FormSubmitException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\ContactUsFormHandler;
use App\Entity\Partner;

/**
 * Class PartnersController
 * @package App\Controller
 */
class PartnersController extends AbstractController
{
    private const QUERY_PARAMETER_PAGE = 'page';
    private const QUERY_PARAMETER_FILTER_TAGS = 'tags';
    private const LIST_PAGE_SIZE = 24;
    private const DETAIL_PAGE_SIZE = 12;

    /**
     * @Route("/partners", name="partners")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param TranslatorInterface $translator
     * @param ContactUsFormHandler $formHandler
     *
     * @return Response
     */
    public function list(
        Request              $request,
        PaginatorInterface   $paginator,
        TranslatorInterface  $translator,
        ContactUsFormHandler $formHandler,
        SeoService $seoService
    ): Response
    {
        $partnerList = $this->getDoctrine()->getRepository(Partner::class)->getPartnerListForFrontPage();

        $partnerList = $paginator->paginate(
            $partnerList,
            $request->query->getInt(self::QUERY_PARAMETER_PAGE, 1),
            self::LIST_PAGE_SIZE
        );

        $companyMessage = $formHandler->buildEntity();
        $form = $formHandler->buildForm($companyMessage);

        if ($request->isMethod('POST')) {
            try {
                $formHandler->handleFormSubmit($request, $form, $companyMessage, $translator);
                $this->addFlash('success', $translator->trans('contacts.ok_send'));

                return $this->redirectToRoute('partners');
            } catch (FormSubmitException $exception) {

            }
        }

        $seo = $seoService->setPage(SeoHelper::PAGE_PARTNERS)->getSeo();

        return $this->render('partners/partners.html.twig', [
            'seo' => $seo,
            'partnerList' => $partnerList,
            'contactUsForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/partner/{slug}", name="partner-detail")
     *
     * @param string $slug
     * @param Request $request
     * @param TranslatorInterface $translator
     * @param ContactUsFormHandler $formHandler
     * @param SeoService $seoService
     *
     * @return Response
     */
    public function detail(
        string $slug,
        Request              $request,
        TranslatorInterface  $translator,
        ContactUsFormHandler $formHandler,
        PaginatorInterface   $paginator,
        SeoService           $seoService
    ): Response
    {
        /** @var Partner $partner */
        $partner = $this
            ->getDoctrine()
            ->getRepository(Partner::class)
            ->findOneBy(['slug' => $slug]);

        if (is_null($partner)) {
            throw new NotFoundHttpException();
        }
        $otherPartnerList = $this
            ->getDoctrine()
            ->getRepository(Partner::class)
            ->findAll();
        foreach ($otherPartnerList as $key => $item){
            if ($partner->getId() === $item->getId() || $item->getIsShownOnFront() !== true){
                unset($otherPartnerList[$key]);
            }
        }
        shuffle($otherPartnerList);

        $tags = [];
        foreach ($partner->getItems() as $item) {
            if ($item->getIsActive() && $item->getCategory() && !in_array($item->getCategory(), $tags)) {
                $tags[] = $item->getCategory();
            }
        }

        $all = $request->query->all();
        $filteredItems = [];

        $tagFilter = null;
        if (!empty($all[self::QUERY_PARAMETER_FILTER_TAGS])) {
            $tagFilter = explode(',', ($all[self::QUERY_PARAMETER_FILTER_TAGS]));
            foreach ($partner->getItems() as $item) {

                if ($item->getCategory() && in_array($item->getCategory()->getId(), $tagFilter) && $item->getIsActive()) {
                    $filteredItems[] = $item;
                }
            }
        }
        if (empty($filteredItems)) {
            foreach ($partner->getItems() as $item) {
                if ($item->getIsActive()){
                    $filteredItems[] = $item;
                }
            }
        }

        $page = null;
        if (!empty($all[self::QUERY_PARAMETER_PAGE])) {
            $page = $all[self::QUERY_PARAMETER_PAGE];
        }

        $items = $paginator->paginate(
            $filteredItems,
            $request->query->getInt(self::QUERY_PARAMETER_PAGE, 1),
            self::DETAIL_PAGE_SIZE
        );

        $companyMessage = $formHandler->buildEntity();
        $form = $formHandler->buildForm($companyMessage);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $this->render('partners/block/studying-materials-block.html.twig',
                    [
                        'items' => $items,
                    ])->getContent(),
            ], Response::HTTP_OK);
        }

        if ($request->isMethod('POST')) {
            try {
                $formHandler->handleFormSubmit($request, $form, $companyMessage, $translator);
                $this->addFlash('success', $translator->trans('contacts.ok_send'));

                return $this->redirectToRoute('partner-detail', [
                    'id' => $partner->getId(),
                ]);
            } catch (FormSubmitException $exception) {

            }
        }

        $seo = $seoService
            ->setPage(SeoHelper::PAGE_PARTNER)
            ->getSeo(['name' => $partner->getName()])
        ;

        return $this->render('partners/detail.html.twig', [
            'seo' => $seoService->merge($seo, $partner->getSeo()),
            'partner' => $partner,
            'items' => $items,
            'tags' => $tags,
            'otherPartnerList' => $otherPartnerList,
            'contactUsForm' => $form->createView(),
            'appliedQueryParams' => [
                self::QUERY_PARAMETER_FILTER_TAGS => $tagFilter,
                self::QUERY_PARAMETER_PAGE => $page,
            ],
            'listAjaxUrl' => $this->generateUrl('partner-detail', [
                'slug' => $partner->getSlug(),
                self::QUERY_PARAMETER_FILTER_TAGS => 'TAGS_VALUES',
                self::QUERY_PARAMETER_PAGE => 'PAGE_VALUE',
            ]),
        ]);
    }
}
