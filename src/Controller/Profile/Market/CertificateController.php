<?php

namespace App\Controller\Profile\Market;

use App\Entity\Market\UserCertificate;
use App\Entity\Market\UserProperty;
use App\Entity\User;
use App\Event\Commodity\Profile\Certificate\CertificateCreateEvent;
use App\Event\Commodity\Profile\Certificate\CertificateUpdateEvent;
use App\Form\Market\UserCertificateFormType;
use App\Repository\Market\UserCertificateRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CertificateController extends ProfileMarketController
{
    private array $requiredRoles = [User::ROLE_SALESMAN, User::ROLE_SERVICE_PROVIDER, User::ROLE_WHOLESALE_BUYER];
    private const MY_CERTIFICATES_PAGE_SIZE = 24;
    private const MY_CERTIFICATES_ACTIONS = [
        'delete',
    ];
    private const MY_CERTIFICATES_ACTION_MAIN = 'edit';

    /**
     * Run certificate saving process.
     *
     * @param UserCertificate $UserCertificate UserCertificate.
     *
     * @return  void
     */
    private function saveCertificate(UserCertificate $UserCertificate): void
    {
        $event = $UserCertificate->getId()
            ? new CertificateUpdateEvent()
            : new CertificateCreateEvent();
        $event->setUserCertificate($UserCertificate);
        $this->eventDispatcher->dispatch($event);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($UserCertificate);
        $entityManager->flush();
    }

    /**
     * @Route(
     *     "/profile/market/my-certificats/edit/{editId}",
     *     name="market_profile_edit_my_certificates"
     * )
     */
    public function editMyCertificate(Request $request, ?UserInterface $user, TranslatorInterface $translator, $editId): Response
    {
        /** @var User|null $user */
        if (!$user) {
            throw new NotFoundHttpException();
        }

        /**
         * @var UserCertificate $certificate
         */
        $certificate = $this->getDoctrine()->getManager()->getRepository(UserCertificate::class)->find($editId);

        if ($user->getId() !== $certificate->getUserProperty()->getUser()->getId()) {
            return $this->redirectToRoute('market_profile_my_certificates');
        }

        if ($certificate->getIsEcology() && $certificate->getApproved()) {
            $this->addFlash('success', $translator->trans('role.success_certificate_edit_rejected'));
            return $this->redirectToRoute('market_profile_my_certificates');
        }
        $form = $this->createForm(UserCertificateFormType::class, $certificate);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $certificate->setUserProperty($user->getUserProperty());
            $this->saveCertificate($certificate);

            /*            $this->addFlash('success', $translator->trans('role.success_certificate_added'));*/
            return $this->redirectToRoute('market_profile_my_certificates');
        }
        return $this->render("profile/market/myCertificates/form.html.twig",
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route(
     *     "/profile/market/my-certificats/delete/{deleteId}",
     *     name="market_profile_delete_my_certificate"
     * )
     */
    public function deleteMyCertificate(Request $request, ?UserInterface $user, TranslatorInterface $translator, $deleteId): Response
    {
        /** @var User|null $user */
        if (!$user) {
            throw new NotFoundHttpException();
        }
        $em = $this->getDoctrine()->getManager();
        $certificate = $em->getRepository(UserCertificate::class)->find($deleteId);
        if ($user->getId() !== $certificate->getUserProperty()->getUser()->getId()) {
            return $this->redirectToRoute('market_profile_my_certificates');
        }
        $em->remove($certificate);
        $em->flush();
        $this->addFlash('success', $translator->trans('role.success_certificate_deleted'));
        return $this->redirectToRoute('market_profile_my_certificates');
    }


    /**
     * @Route("/profile/market/my-certificats/create", name="market_profile_create_certificate")
     */
    public function createCert(Request $request, ?UserInterface $user, TranslatorInterface $translator): Response
    {
        /** @var User|null $user */
        if (!$user) {
            return $this->redirectToRoute('app_register');
        }
        $certificate = new UserCertificate();

        $form = $this->createForm(UserCertificateFormType::class, $certificate);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

                $certificate->setUserProperty($user->getUserProperty());
                $this->saveCertificate($certificate);

                return $this->redirectToRoute('market_profile_my_certificates');
            }
        }
        return $this->render("profile/market/myCertificates/form.html.twig",
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/profile/market/my-certificats", name="market_profile_my_certificates")
     */
    public function myCertificates(Request $request, ?UserInterface $user, TranslatorInterface $translator): Response
    {
        /** @var User|null $user */
        if (!$user) {
            return $this->redirectToRoute('app_register');
        }
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $appliedFilter = $this->parseFilter($request);
        $requiredRoles = $this->requiredRoles;
        foreach ($requiredRoles as $role) {
            if (in_array($role, $currentUser->getRoles())) {
                return $this->render('profile/market/myCertificates/index.html.twig', [
                    'items' => $this->getMyCertificates($appliedFilter),
                    'filter' => $appliedFilter,
                    'availableSortValues' => UserCertificateRepository::getSortList(),
                    'actions' => self::MY_CERTIFICATES_ACTIONS,
                    'mainAction' => self::MY_CERTIFICATES_ACTION_MAIN,
                    'currentPage' => $appliedFilter['page'],
                    'paginationName' => 'page',
                ]);
            }
        }

        return $this->render('profile/market/myCommodities/roleRequired.html.twig', [
            'role' => $requiredRoles[0]
        ]);
    }

    /**
     * @Route("/profile/market/my-certificats/xhr", name="market_profile_my_certificates_xhr")
     */
    public function myCertificatesXHR(Request $request, ?UserInterface $user, TranslatorInterface $translator): Response
    {
        $appliedFilter = $this->parseFilter($request);
        $listPageRouteName = 'market_profile_my_certificates';

        return new JsonResponse([
            'url' => $this->generateUrl($listPageRouteName, $appliedFilter),
            'itemsList' => $this->render('profile/market/myCertificates/itemsListing.html.twig', [
                'items' => $this->getMyCertificates($appliedFilter),
                'filter' => $appliedFilter,
                'availableSortValues' => UserCertificateRepository::getSortList(),
                'actions' => self::MY_CERTIFICATES_ACTIONS,
                'mainAction' => self::MY_CERTIFICATES_ACTION_MAIN,
                'currentPage' => $appliedFilter['page'],
                'paginationName' => 'page',
            ])
                ->getContent(),
        ]);
    }

    /**
     * Get my certs.
     *
     * @param array $appliedFilter Filter.
     * @param string $UserCertificateType UserCertificate type.
     *
     * @return  PaginationInterface         Items.
     */
    private function getMyCertificates(
        array  $appliedFilter,
        string $UserCertificateType = ''
    ): PaginationInterface
    {
        /** @var User|null $currentUser */
        $currentUser = $this->getUser();
        $UserCertificateFilter = [
            'search' => $appliedFilter['search'],
            'certificateTypeApproved' => $appliedFilter['certificateTypeApproved'] ?? true,
            'certificateTypeNotApproved' => $appliedFilter['certificateTypeNotApproved'] ?? true,
            'eco' => $appliedFilter['eco'] ?? true,
            'default' => $appliedFilter['default'] ?? true,
            'user' => $this->getUser()->getId()
        ];

        $itemsQuery = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(UserCertificate::class)
            ->listFilter(
                $appliedFilter['sortField'],
                array_merge($UserCertificateFilter, [
                    'user' => $currentUser->getId(),
                ])
            );

        return $this->paginate(
            $itemsQuery,
            $appliedFilter['page'],
            self::MY_CERTIFICATES_PAGE_SIZE
        );
    }
    /**
     * Parse filter from request.
     *
     * @param Request $request Request.
     * @param string $UserCertificate UserCertificate type.
     *
     * @return  array                       Parsed filter.
     */
    private function parseFilter(Request $request, string $UserCertificateType = ''): array
    {
        $requestData = $request->getMethod() === 'POST'
            ? $request->request->all()
            : $request->query->all();
        $basicFilter = [];
        $UserCertificateFilter = [];

        $searchValue = (string)($requestData['search'] ?? '');
        $basicFilter['search'] = strlen($searchValue) > 0 ? $searchValue : null;
        $sortAvailableValues = UserCertificateRepository::getSortList();
        $sortValueIncome = $requestData['sortField'] ?? null;
        $basicFilter['sortField'] = in_array($sortValueIncome, $sortAvailableValues)
            ? $sortValueIncome
            : $sortAvailableValues[0] ?? '';

        $pageValueIncome = (int)($requestData['page'] ?? 0);
        $basicFilter['page'] = $pageValueIncome > 0 ? $pageValueIncome : 1;

        $filter = [];

        $requestData['certificateTypeApproved'] = (int)($requestData['certificateTypeApproved'] ?? null);
        $requestData['certificateTypeNotApproved'] = (int)($requestData['certificateTypeNotApproved'] ?? null);
        $requestData['eco'] = (int)($requestData['eco'] ?? null);
        $requestData['default'] = (int)($requestData['default'] ?? null);

        return array_merge(
            $requestData,
            $basicFilter,
            $filter
        );
    }
}
