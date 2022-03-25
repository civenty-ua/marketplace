<?php

namespace App\Controller;

use App\Helper\SeoHelper;
use App\Service\SeoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\Form\Exception\RuntimeException as FormSubmitException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\ContactUsFormHandler;
use App\Entity\{
    Expert,
    ExpertType,
    Tags,
};

/**
 * Class ExpertController
 * @package App\Controller
 */
class ExpertController extends AbstractController
{
    private const QUERY_PARAMETER_FILTER_TAGS = 'tags';
    private const QUERY_PARAMETER_PAGE = 'page';
    private const LIST_PAGE_SIZE = 24;

    /**
     * @Route("/experts", name="experts")
     *
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param TranslatorInterface $translator
     * @param ContactUsFormHandler $formHandler
     *
     * @return  Response
     */
    public function list(
        Request              $request,
        PaginatorInterface   $paginator,
        TranslatorInterface  $translator,
        ContactUsFormHandler $formHandler,
        SeoService $seoService
    ): Response
    {
        $statement = $this
            ->getDoctrine()
            ->getConnection()
            ->prepare("SELECT distinct(tags_id) FROM expert_tags");
        $statement->execute();
        $expertTagsIdList = $statement->fetchAll();
        $tagsIdList = [];
        foreach ($expertTagsIdList as $expertTagsId) {
            $tagsIdList[] = $expertTagsId['tags_id'];
        }

        $all = $request->query->all();

        $tagFilter = null;
        if (!empty($all[self::QUERY_PARAMETER_FILTER_TAGS])) {
            $tagFilter = explode(',', ($all[self::QUERY_PARAMETER_FILTER_TAGS]));
        }

        $page = null;
        if (!empty($all[self::QUERY_PARAMETER_PAGE])) {
            $page = $all[self::QUERY_PARAMETER_PAGE];
        }

        $experts = $this->getDoctrine()->getRepository(Expert::class)->getFilterExperts($tagFilter);

        $experts = $paginator->paginate(
            $experts,
            $request->query->getInt(self::QUERY_PARAMETER_PAGE, 1),
            self::LIST_PAGE_SIZE
        );

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'content' => $this->render('blocks/item-panel/list-experts.html.twig',
                    [
                        'experts' => $experts,
                    ])->getContent(),
            ], Response::HTTP_OK);
        }

        $companyMessage = $formHandler->buildEntity();
        $form = $formHandler->buildForm($companyMessage);

        if ($request->isMethod('POST')) {
            try {
                $formHandler->handleFormSubmit($request, $form, $companyMessage, $translator);
                $this->addFlash('success', $translator->trans('contacts.ok_send'));

                return $this->redirectToRoute('experts');
            } catch (FormSubmitException $exception) {

            }
        }

        $seo = $seoService->setPage(SeoHelper::PAGE_EXPERTS)->getSeo();

        return $this->render('experts/full.html.twig', [
            'seo' => $seo,
            'expertTypes' => $this
                ->getDoctrine()
                ->getRepository(ExpertType::class)
                ->findAll(),
            'experts' => $experts,
            'tags' => $this
                ->getDoctrine()
                ->getRepository(Tags::class)
                ->findBy(['id' => $tagsIdList]),
            'appliedQueryParams' => [
                self::QUERY_PARAMETER_FILTER_TAGS => $tagFilter,
                self::QUERY_PARAMETER_PAGE => $page,
            ],
            'contactUsForm' => $form->createView(),
            'listAjaxUrl' => $this->generateUrl('experts', [
                self::QUERY_PARAMETER_FILTER_TAGS => 'TAGS_VALUES',
                self::QUERY_PARAMETER_PAGE => 'PAGE_VALUE',
            ]),
        ]);
    }

    /**
     * @Route("/expert/{slug}", name="expert-detail")
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
        string               $slug,
        Request              $request,
        TranslatorInterface  $translator,
        ContactUsFormHandler $formHandler,
        SeoService           $seoService
    ): Response
    {
        /** @var Expert $expert */
        $expert = $this
            ->getDoctrine()
            ->getRepository(Expert::class)
            ->findOneBy(['slug' => $slug]);
        if (is_null($expert)) {
            throw new NotFoundHttpException();
        }
        $statement = $this
            ->getDoctrine()
            ->getConnection()
            ->prepare("SELECT id FROM expert");
        $statement->execute();
        $expertIdList = $statement->fetchAll();
        $first = array_key_first($expertIdList);
        $last = array_key_last($expertIdList);
        $expertIdListNew = [];

        for ($i = 0; $i < 9; $i++) {
            $rand = rand($first, $last);
            $expertIdListNew[] = $expertIdList[$rand]['id'];
        }

        $filteredItems = [];

        $companyMessage = $formHandler->buildEntity();
        $form = $formHandler->buildForm($companyMessage);
        foreach ($expert->getItems() as $item) {
            if ($item->getIsActive()){
                $filteredItems[] = $item;
            }
        }
        if ($request->isMethod('POST')) {
            try {
                $formHandler->handleFormSubmit($request, $form, $companyMessage, $translator);
                $this->addFlash('success', $translator->trans('contacts.ok_send'));

                return $this->redirectToRoute('expert-detail', [
                    'id' => $expert->getId(),
                ]);
            } catch (FormSubmitException $exception) {

            }
        }

        $seo = $seoService
            ->setPage(SeoHelper::PAGE_EXPERT)
            ->getSeo(['name' => $expert->getName()])
        ;

        return $this->render('experts/detail.html.twig', [
            'seo' => $seoService->merge($seo, $expert->getSeo()),
            'expert' => $expert,
            'items' => $filteredItems,
            'otherExpertList' => $this
                ->getDoctrine()
                ->getRepository(Expert::class)
                ->findBy(['id' => $expertIdListNew]),
            'contactUsForm' => $form->createView(),
        ]);
    }
}
