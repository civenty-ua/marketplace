<?php
declare(strict_types = 1);

namespace App\Controller\Market;

use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\User;
/**
 * Commodities common functional controller.
 *
 * @package App\Controller
 */
class CommoditiesCommonController extends AbstractController
{
    /**
     * @Route(
     *     "/marketplace/user/ajax/get-field",
     *     name     = "market_ajax_get_user_field",
     *     methods  = "POST"
     * )
     */
    public function getUserField(Request $request): Response
    {
        /** @var User|null $user */
        $userId     = $request->request->get('userId');
        $value      = $request->request->get('value');
        $user       = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($userId);
        $response   = null;

        if ($user) {
            switch ($request->request->get('field')) {
                case 'phone':
                    foreach ($user->getPhones() as $phone) {
                        if ($phone->getId() === (int) $value) {
                            $response = $phone->getPhone();
                            break;
                        }
                    }
                    break;
                case 'mainPhone':
                    $response   = $user->getPhone();
                    break;
                case 'email':
                    $response   = $user->getEmail();
                    break;
                default:
            }
        }

        return new JsonResponse([
            'value' => $response,
        ]);
    }
}
