<?php

namespace App\Controller\Admin;

use DateTime;
use EasyCorp\Bundle\EasyAdminBundle\Config\{
    Dashboard,
    MenuItem,
};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Admin\Market\{AgreementBidOfferCrudController,
    BidOfferCrudController,
    CategoryProductCrudController,
    CategoryServiceCrudController,
    DealOfferCrudController,
    KitAgreementNotificationCrudController,
    OfferReviewCrudController,
    PriceOfferCrudController,
    SystemMessageCrudController};
use App\Entity\Market\Notification\SystemMessage;
use App\Entity\Market\Notification\OfferReview;
use App\Entity\Market\Notification\KitAgreementNotification;
use App\Entity\Market\Notification\DealOffer;
use App\Entity\Market\Notification\BidOffer;
use App\Entity\{Article,
    Activity,
    Category,
    Comment,
    CompanyMessages,
    Contact,
    Course,
    Crop,
    DeadUrl,
    Expert,
    ExpertType,
    FeedbackForm,
    ItemRegistration,
    Lesson,
    LessonModule,
    Market\CompanyType,
    Market\LegalCompanyType,
    Market\Phone,
    Market\UserCertificate,
    Market\RequestRole,
    News,
    Occurrence,
    Options,
    Other,
    Page,
    Partner,
    Review,
    Tags,
    User,
    UserDownload,
    UserItemFeedback,
    UserToUserReview,
    VideoItem,
    Webinar,
    Market\Category as MarketCategory,
    Market\Attribute as MarketCategoryAttribute,
    Market\CommodityKit,
    Market\CommodityProduct,
    Market\CommodityService,
    TextType,
    TextBlocks
};

/**
 * Class DashboardController
 */
class DashboardController extends AbstractDashboardController
{
    private const USERS_REGIONS_CHART_COUNT = 5;

    /**
     * @Route("/admin", name="admin", options={"i18n"=false})
     */
    public function index(): Response
    {
        $dateToday = new DateTime('today');
        $dateTomorrow = new DateTime('tomorrow');
        $dateChartsStart = (new DateTime('today'))->modify('-30 day');

        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $usersAllTime = [];
        foreach ([User::GENDER_FEMALE, User::GENDER_MALE, User::GENDER_NULL,] as $gender) {
            $usersAllTime[$gender] = $userRepository
                ->countMainDashboardUserFilter([
                    'gender' => [$gender],
                    'isBanned' => 0,
                    '!roles' => [
                        User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                    ],
                ]);
        }

        $usersToday = [];
        foreach ([User::GENDER_FEMALE, User::GENDER_MALE, User::GENDER_NULL,] as $gender) {
            $usersToday[$gender] = $userRepository->countMainDashboardUserFilter([
                'gender' => [User::GENDER_FEMALE],
                'today' => true,
                'isBanned' => 0,
                '!roles' => [
                    User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                ],
            ]);
        }

        $usersChartData = $userRepository->getRegistrationsCountPerTime(
            $dateChartsStart,
            $dateTomorrow,
            [
                'isBanned' => 0,
                '!roles' => [
                    User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                ],
                'instance' => 'user'
            ]
        );
        $usersRegionsData = $userRepository->getCountPerRegions('uk',[
            'isBanned' => 0,
            '!roles' => [
                User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
            ],
        ]);
        $articlesChartData = $this
            ->getDoctrine()
            ->getRepository(Article::class)
            ->getCountPerTime($dateChartsStart, $dateTomorrow);
        $coursesRegistrationsChartData = $this
            ->getDoctrine()
            ->getRepository(ItemRegistration::class)
            ->getCountPerTime($dateChartsStart, $dateTomorrow, [[
                'field' => 'itemId',
                'join' => Course::class,
            ]]);
        $webinarsChartData = $this
            ->getDoctrine()
            ->getRepository(ItemRegistration::class)
            ->getCountPerTime($dateChartsStart, $dateTomorrow, [[
                'field' => 'itemId',
                'join' => Webinar::class,
            ]]);
        return $this->render('admin/index.html.twig', [
            'users' => [
                'registeredToday' => [
                    'women' => $usersToday[User::GENDER_FEMALE],
                    'men' => $usersToday[User::GENDER_MALE],
                    'unknown' => $usersToday[User::GENDER_NULL],
                    'total' => array_sum($usersToday),
                ],
                'registeredAllTime' => [
                    'women' => $usersAllTime[User::GENDER_FEMALE],
                    'men' => $usersAllTime[User::GENDER_MALE],
                    'unknown' => $usersAllTime[User::GENDER_NULL],
                    'total' => array_sum($usersAllTime),
                    'registrationChart' => $this->formatChartData($usersChartData),
                    'topRegionsChart' => $this->formatUsersRegionsData($usersRegionsData),
                ],
            ],
            'articles' => [
                'chart' => $this->formatChartData($articlesChartData),
            ],
            'coursesRegistrations' => [
                'chart' => $this->formatChartData($coursesRegistrationsChartData),
            ],
            'webinars' => [
                'chart' => $this->formatChartData($webinarsChartData),
            ],
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('???????? ??????????????????');
    }

    public function configureMenuItems(): iterable
    {
        $countNewsRoles = $this->getDoctrine()->getManager()->getRepository(RequestRole::class)->getCountNewRole();
        if (is_null($countNewsRoles)) $countNewsRoles = 0;
        yield MenuItem::linkToUrl('?????????????????????? ???? ????????', 'fas fa-home', '/');
        yield MenuItem::linkToDashboard('?????????????? ????????????', 'fas fa-tachometer-alt');
        if (
            $this->isGranted(User::ROLE_ADMIN_EDUCATION) ||
            $this->isGranted(User::ROLE_SUPER_ADMIN)
        ) {
            yield MenuItem::section('????????-????????');
            yield MenuItem::linkToCrud('????????????', 'fas fa-newspaper', Article::class);
            yield MenuItem::linkToCrud('????????????', 'fas fa-newspaper', News::class);
            yield MenuItem::subMenu('??????????????', 'fa fa-eye')->setSubItems([
                MenuItem::linkToCrud('??????????????', 'fas fa-eye', Webinar::class),
                MenuItem::linkToCrud('?????????????????????????? ??????????????????????', 'fas fa-user-friends', ItemRegistration::class)
                    ->setController(WebinarRegistrationCrudController::class),
            ]);
            yield MenuItem::subMenu('??????????', 'fas fa-tv')->setSubItems([
                MenuItem::linkToCrud('??????????', 'fas fa-tv', Occurrence::class),
                MenuItem::linkToCrud('?????????????????????????? ??????????????????????', 'fas fa-user-friends', ItemRegistration::class)
                    ->setController(OccurrenceRegistrationCrudController::class),
            ]);
            yield MenuItem::subMenu('??????????', 'fa fa-list')->setSubItems([
                MenuItem::linkToCrud(
                    '??????????',
                    'fas fa-graduation-cap',
                    Course::class
                )
                    ->setController(CourseCrudController::class),
                MenuItem::linkToCrud(
                    '????????????',
                    'fas fa-archive',
                    LessonModule::class
                ),
                MenuItem::linkToCrud(
                    '??????????',
                    'fas fa-laptop',
                    Lesson::class
                ),
                MenuItem::linkToCrud(
                    'admin.itemRegistration.titles.index',
                    'fas fa-user-friends',
                    ItemRegistration::class
                )
                    ->setController(CourseRegistrationCrudController::class),
                MenuItem::linkToCrud(
                    'admin.courseStatistic.titles.index',
                    'fas fa-chart-bar',
                    Course::class
                )
                    ->setController(CourseStatisticCrudController::class),
            ]);
            yield MenuItem::linkToCrud('????????', 'fas fa-video', Other::class);
            yield MenuItem::linkToCrud('??????????', 'fas fa-video', VideoItem::class);
            yield MenuItem::linkToCrud(
                '?????????? ???????????????????? ???????????????',
                'fab fa-wpforms',
                FeedbackForm::class
            );
            yield MenuItem::linkToCrud(
                '??????????????????',
                'fas fa-comment',
                Comment::class
            );
        }

        if (
            $this->isGranted(User::ROLE_ADMIN_MARKET) ||
            $this->isGranted(User::ROLE_SUPER_ADMIN)
        ) {

            yield MenuItem::section('admin.market.title');
            yield MenuItem::linkToCrud(
                'admin.market.product.titles.index',
                'fas fa-gift',
                CommodityProduct::class
            );
            yield MenuItem::linkToCrud(
                'admin.market.service.titles.index',
                'fas fa-handshake',
                CommodityService::class
            );
            yield MenuItem::linkToCrud(
                '????????????????????',
                'fas fa-gift',
                CommodityKit::class
            );
            yield MenuItem::linkToCrud(
                '??????????',
                'fas fa-handshake',
                BidOffer::class
            )->setController(AgreementBidOfferCrudController::class);
            yield MenuItem::subMenu('??????????????????', 'fa fa-list')->setSubItems([
                MenuItem::linkToCrud(
                    'admin.market.categoryProduct.titles.index',
                    'fas fa-sitemap',
                    MarketCategory::class
                )->setController(CategoryProductCrudController::class),
                MenuItem::linkToCrud(
                    'admin.market.categoryService.titles.index',
                    'fas fa-sitemap',
                    MarketCategory::class
                )->setController(CategoryServiceCrudController::class),
                MenuItem::linkToCrud(
                    'admin.market.attribute.titles.index',
                    'fas fa-sitemap',
                    MarketCategoryAttribute::class
                )
            ]);

            yield MenuItem::subMenu('????????????????????????', 'fa fa-mail-bulk')->setSubItems([
                MenuItem::linkToCrud(
                    '???????????????????? ???????????? ?????????? ???? ??????????????',
                    'fas fa-comments-dollar',
                    BidOffer::class
                )->setController(BidOfferCrudController::class),
                MenuItem::linkToCrud(
                    '???????????????????? ????????',
                    'fas fa-comments-dollar',
                    BidOffer::class
                )->setController(PriceOfferCrudController::class),
                MenuItem::linkToCrud(
                    '????????????????????, ???????? ???????????????? ????????????????????',
                    'fas fa-comment-dollar',
                    KitAgreementNotification::class
                )->setController(KitAgreementNotificationCrudController::class),

                MenuItem::linkToCrud(
                    '????????????????????',
                    'fas fa-question',
                    OfferReview::class
                )->setController(OfferReviewCrudController::class),
                MenuItem::linkToCrud(
                    '???????????????? ????????????????????????',
                    'fas fa-robot',
                    SystemMessage::class
                )->setController(SystemMessageCrudController::class),
            ]);


            yield MenuItem::subMenu('??????????????????', 'fa fa-list')->setSubItems([
                MenuItem::linkToCrud('?????? ????????????????', 'fas fa-building', CompanyType::class),
                MenuItem::linkToCrud('?????????????????? ?????? ????????????????', 'fas fa-id-card-alt', LegalCompanyType::class),
                MenuItem::linkToCrud('???????? ??????????????', 'fas fa-id-card-alt', TextType::class),
                MenuItem::linkToCrud('???????????????? ??????????', 'fas fa-id-card-alt', TextBlocks::class)
            ]);

            yield MenuItem::section('?????????????????? ???? ????????????????????????');
            yield MenuItem::linkToRoute(
                '?????????????????? ???? ????????????????????????',
                'fas fa-user',
                'adminAnalyticUser',
                ['show' => 'value']);
            yield MenuItem::subMenu('?????????????????? ???? ??????????????????', 'fas fa-user')->setSubItems([
                MenuItem::linkToRoute(
                    '???? ?????????? ????????????????????',
                    'fas fa-user',
                    'adminAnalyticUserSalers',
                    ['type' => 'form', 'show' => 'value']
                ),
                MenuItem::linkToRoute(
                    '???? ????????????????????',
                    'fas fa-user',
                    'adminAnalyticUserSalers',
                    ['type' => 'cats', 'show' => 'value']
                ),
                MenuItem::linkToRoute(
                    '???? ????????????????',
                    'fas fa-user',
                    'adminAnalyticUserSalers',
                    ['type' => 'regions', 'show' => 'value']
                ),
            ]);
            yield MenuItem::subMenu('?????????????????? ???? ???????????????????????????? ????????????', 'fas fa-user')->setSubItems([
                MenuItem::linkToRoute(
                    '???? ?????????? ????????????????????',
                    'fas fa-user',
                    'adminAnalyticUserServiceProviders',
                    ['type' => 'form', 'show' => 'value']
                ),
                MenuItem::linkToRoute(
                    '???? ????????????????????',
                    'fas fa-user',
                    'adminAnalyticUserServiceProviders',
                    ['type' => 'cats', 'show' => 'value']
                ),
                MenuItem::linkToRoute(
                    '???? ????????????????',
                    'fas fa-user',
                    'adminAnalyticUserServiceProviders',
                    ['type' => 'regions', 'show' => 'value']
                ),
            ]);

            yield MenuItem::section('?????????????????? ???? ?????????????????????????? ????????????????????');
            yield MenuItem::subMenu('?????????????????? ???? ??????????????', 'fas fa-user')->setSubItems([
                MenuItem::linkToRoute(
                    '????????????????',
                    'fas fa-user',
                    'adminAnalyticGoods',
                    ['type' => 'all', 'show' => 'value']
                ),
                MenuItem::linkToRoute(
                    '???? ?????????? ??????????????????????',
                    'fas fa-user',
                    'adminAnalyticGoods',
                    ['type' => 'form', 'show' => 'value']
                ),
                MenuItem::linkToRoute(
                    '???? ????????????????????',
                    'fas fa-user',
                    'adminAnalyticGoods',
                    ['type' => 'cats', 'show' => 'value']
                ),
                MenuItem::linkToRoute(
                    '???? ????????????????',
                    'fas fa-user',
                    'adminAnalyticGoods',
                    ['type' => 'regions', 'show' => 'value']
                ),
            ]);
            yield MenuItem::subMenu('?????????????????? ???? ????????????????', 'fas fa-user')->setSubItems([
                MenuItem::linkToRoute(
                    '???? ????????????????????',
                    'fas fa-user',
                    'adminAnalyticServices',
                    ['type' => 'cats', 'show' => 'value']
                ),
                MenuItem::linkToRoute(
                    '???? ????????????????',
                    'fas fa-user',
                    'adminAnalyticServices',
                    ['type' => 'regions', 'show' => 'value']
                ),
            ]);
        }

        yield MenuItem::section('????????????????????????');
        yield MenuItem::linkToCrud('????????????????', 'fas fa-newspaper', Page::class);

        yield MenuItem::linkToCrud('?????????????? ????????????????????', 'fas fa-comments', Review::class);
        yield MenuItem::linkToCrud(
            '?????????? ???????????????????? ???????????????',
            'fab fa-wpforms',
            FeedbackForm::class
        );
        yield MenuItem::subMenu('??????????????????', 'fa fa-list')->setSubItems([
            //MenuItem::linkToCrud('?????? ????????????????', 'fas fa-file-alt', TypePage::class),
            MenuItem::linkToCrud('?????? ??????????????????', 'fas fa-file-alt', ExpertType::class),
            MenuItem::linkToCrud('??????????????????', 'fas fa-sitemap', Category::class),
            MenuItem::linkToCrud('????????????????', 'fas fa-building', Partner::class),
            MenuItem::linkToCrud('????????????????', 'fas fa-user-friends', Expert::class),
            MenuItem::linkToCrud('????????', 'fas fa-tags', Tags::class),
            MenuItem::linkToCrud('????????????????', 'fas fa-list-ol', Crop::class),
            MenuItem::linkToCrud('????????????????????', 'fas fa-list-ol', Activity::class),
        ])->setPermission(User::ROLE_ADMIN_EDUCATION);

        yield MenuItem::section('??????????????????????????????');
        yield MenuItem::subMenu('??????????????????????', 'fas fa-user')->setSubItems([
            MenuItem::linkToCrud('????????????', 'fas fa-list', User::class),
            MenuItem::linkToCrud('????????????????', 'fas fa-phone', Phone::class),
            MenuItem::linkToCrud('??????????????????????', 'fas fa-award', UserCertificate::class)->setPermission(User::ROLE_ADMIN_MARKET),
            MenuItem::linkToCrud('???????????????????????? ?????? ????????????????????????', 'fas fa-envelope', CompanyMessages::class),
            MenuItem::linkToCrud('?????????????????? ????????????????????????', 'fas fa-question', UserItemFeedback::class),
            MenuItem::linkToCrud(
                '??????????????????',
                'fas fa-comment',
                Comment::class
            ),
            MenuItem::linkToCrud('?????????????? ?????? ????????????????????????', 'fas fa-envelope', UserToUserReview::class),
            MenuItem::linkToCrud('?????????????????????? ??????????', 'fas fa-file', UserDownload::class),
            MenuItem::linkToCrud('???????????? ???????? (' . $countNewsRoles . ')', 'fas fa-user-tag', RequestRole::class)->setPermission(User::ROLE_ADMIN_MARKET)
        ]);
        yield MenuItem::linkToCrud('???????? ????????????????', 'fas fa-user', Contact::class);
        yield MenuItem::linkToCrud('????????????????????????', 'fas fa-cogs', Options::class)->setPermission(User::ROLE_SUPER_ADMIN);
        yield MenuItem::linkToCrud('?????????????????????????????? ????????????????', 'fas fa-link', DeadUrl::class)->setPermission(User::ROLE_SUPER_ADMIN);


    }

    /**
     * Format data for chart.
     *
     * @param array $data Data as is, key -> value set
     *
     * @return  array                       Data formatted, where
     *                                      each value is a summary of previous values.
     */
    private function formatChartData(array $data): array
    {
        $keys = array_keys($data);
        $values = array_values($data);
        $result = [];

        foreach ($values as $key => $value) {
            $previousValues = array_slice($values, 0, $key + 1);
            $result[] = array_sum($previousValues);
        }

        return array_combine($keys, $result);
    }

    /**
     * Format data for chart.
     *
     * @param array $data Data as is, key -> value set
     *
     * @return  array                       Data formatted, where
     *                                      each value is a summary of previous values.
     */
    private function formatUsersRegionsData(array $data): array
    {
        $countValues = array_keys(array_flip($data));
        arsort($countValues);
        $result = [];

        foreach ($countValues as $value) {
            foreach ($data as $region => $userAmount) {
                if ($userAmount === $value) {
                    $result[$region] = $userAmount;
                    unset($data[$region]);

                    if (count($result) === self::USERS_REGIONS_CHART_COUNT || count($data) === 0) {
                        break 2;
                    }
                }
            }
        }

        return $result;
    }
}