<?php

namespace App\Controller\Admin\Analytic;

use App\Entity\Crop;
use App\Entity\Region;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnalyticUserController extends AnalyticBaseController
{
    /**
     * @Route("/admin/analytic/user/{show}", name="adminAnalyticUser", options={"i18n"=false})
     */
    public function userAnalytic($show): Response
    {
        /** @var UserRepository $repo */
        $repo = $this->getDoctrine()->getRepository(User::class);
        foreach (User::$rolesInRequestRoles as $key => $role) {
            $data[User::getNameRolesByCode($key)] =
                $repo->getTotalCount(null,[
                    'roles' => [$role => $role],
                    '!roles' => [
                        User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                    ],
                ]);
        }
        $data = array_merge(['ALL' => array_sum($data)], $data);
        return $this->render(null, [
            'analytic_page_title' => 'Аналітика по користувачам',
            'data' => $data,
            'show' => $show,
        ]);
    }

    /**
     * @Route("/admin/analytic/user/salers/{type}/{show}", name="adminAnalyticUserSalers", options={"i18n"=false})
     */
    public function userSalersAnalytic($type, $show): Response
    {
        /** @var UserRepository $repo */
        $repo = $this->getDoctrine()->getRepository(User::class);
        $role = User::ROLE_SALESMAN;
        switch ($type) {
            case 'form':
                $data['Чоловіки'] = $repo->getTotalCount(null, [
                    'gender' => User::GENDER_MALE,
                    'roles' => [$role],
                    '!roles' => [
                        User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                    ],
                    ]);

                $data['Жінки'] = $repo->getTotalCount(null,[
                    'gender' => User::GENDER_FEMALE,
                    'roles' => [$role],
                    '!roles' => [
                        User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                    ],
                ]);
                break;

            case 'cats':
                $cropList = $this->getDoctrine()->getRepository(Crop::class)
                    ->createQueryBuilder('cr')
                    ->getQuery()
                    ->getResult();

                /** @var Crop $crop */
                foreach ($cropList as $crop)
                {
                    $data[$crop->translate()->getName()] = $repo->getTotalCount(null,[
                        'crop' => $crop->getId(),
                        'roles' => [$role],
                        '!roles' => [
                            User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                        ],
                    ]);
                }
                break;
            case 'regions':
                $List = $this->getDoctrine()->getRepository(Region::class)
                    ->createQueryBuilder('cr')  /** @var User|null $currentUser */
                    ->getQuery()
                    ->getResult();

                /** @var Crop $crop */
                foreach ($List as $region)
                {
                    $data[$region->translate()->getName()] = $repo->getTotalCount(null,[
                        'region' => $region->getId(),
                        'roles' => [$role],
                        '!roles' => [
                            User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                        ],
                    ]);
                }
                break;
        }

        return $this->render(null, [
            'analytic_page_title' => 'Аналітика по продавцям',
            'data' => $data,
            'show' => $show,
        ]);
    }

    /**
     * @Route("/admin/analytic/user/service-providers/{type}/{show}", name="adminAnalyticUserServiceProviders", options={"i18n"=false})
     */
    public function userServiceProvidersAnalytic($type, $show): Response
    {
        /** @var UserRepository $repo */
        $repo = $this->getDoctrine()->getRepository(User::class);

        $role = User::ROLE_SERVICE_PROVIDER;

        switch ($type) {
            case 'form':
                $data['Чоловіки'] = $repo->getTotalCount(null,[
                    'gender' => User::GENDER_MALE,
                    'roles' => [$role],
                    '!roles' => [
                        User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                    ],
                ]);
                $data['Жінки'] = $repo->getTotalCount(null,[
                    'gender' => User::GENDER_FEMALE,
                    'roles' => [$role],
                    '!roles' => [
                        User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                    ],
                ]);
                break;
            case 'cats':
                $cropList = $this->getDoctrine()->getRepository(Crop::class)
                    ->createQueryBuilder('cr')
                    ->getQuery()
                    ->getResult();

                /** @var Crop $crop */
                foreach ($cropList as $crop)
                {
                    $data[$crop->translate()->getName()] = $repo->getTotalCount(null,[
                        'crop' => $crop->getId(),
                        'roles' => [$role => $role],
                        '!roles' => [
                            User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                        ],
                    ]);
                }
                break;
            case 'regions':
                $List = $this->getDoctrine()->getRepository(Region::class)
                    ->createQueryBuilder('cr')
                    ->getQuery()
                    ->getResult();

                /** @var Crop $crop */
                foreach ($List as $region)
                {
                    $data[$region->translate()->getName()] = $repo->getTotalCount(null,
                        [
                            'region' => $region->getId(),
                            'roles' => [$role => $role],
                            '!roles' => [
                                User::ROLE_ADMIN_EDUCATION, User::ROLE_ADMIN_MARKET, User::ROLE_SUPER_ADMIN,
                            ],
                        ]);
                }
                break;
        }

        return $this->render(null, [
            'analytic_page_title' => 'Аналітика по постачальникам послуг',
            'data' => $data,
            'show' => $show,
        ]);
    }

}
