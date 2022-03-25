<?php
declare(strict_types = 1);

namespace App\Controller\Market;

use Symfony\Component\HttpFoundation\{
    Response,
    JsonResponse,
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\{
    User,
    Market\Commodity,
    Market\CommodityFavorite,
    Market\UserFavorite,
};
/**
 * Favorites actions controller.
 *
 * @package App\Controller
 */
class FavoriteController extends AbstractController
{
    private ?User $user;

    public function __construct(Security $security)
    {
        $this->user = $security->getUser();
    }
    /**
     * @Route(
     *     "/marketplace/commodity-to-favorite/{id}",
     *     name     = "commodity_to_favorite_toggle",
     *     methods  = "POST"
     * )
     */
    public function commodityToggle(int $id): Response
    {
        $entityManager  = $this->getDoctrine()->getManager();
        $commodity      = $this->findCommodity($id);
        $existRecord    = $commodity ? $this->findCommodityFavorite($commodity) : null;

        if ($this->user && $commodity) {
            if ($existRecord) {
                $entityManager->remove($existRecord);
                $entityManager->flush();
                $isAdded = false;
            } else {
                $newRecord = $this->createCommodityFavorite($commodity);
                $entityManager->persist($newRecord);
                $entityManager->flush();
                $isAdded = true;
            }
        } else {
            $isAdded = false;
        }

        return new JsonResponse([
            'isAdded' => $isAdded,
        ]);
    }
    /**
     * @Route(
     *     "/marketplace/user-to-favorite/{id}",
     *     name     = "user_to_favorite_toggle",
     *     methods  = "POST"
     * )
     */
    public function userToggle(int $id): Response
    {
        $entityManager  = $this->getDoctrine()->getManager();
        $userToFavorite = $this->findUser($id);
        $existRecord    = $userToFavorite ? $this->findUserFavorite($userToFavorite) : null;

        if ($this->user && $userToFavorite) {
            if ($existRecord) {
                $entityManager->remove($existRecord);
                $entityManager->flush();
                $isAdded = false;
            } else {
                $newRecord = $this->createUserFavorite($userToFavorite);
                $entityManager->persist($newRecord);
                $entityManager->flush();
                $isAdded = true;
            }
        } else {
            $isAdded = false;
        }

        return new JsonResponse([
            'isAdded' => $isAdded,
        ]);
    }
    /**
     * Try to find commodity by ID.
     *
     * @param   int $id                     ID.
     *
     * @return  Commodity|null              Commodity if any.
     */
    private function findCommodity(int $id): ?Commodity
    {
        return $this
            ->getDoctrine()
            ->getRepository(Commodity::class)
            ->find($id);
    }
    /**
     * Try to find user by ID.
     *
     * @param   int $id                     ID.
     *
     * @return  User|null                   User if any.
     */
    private function findUser(int $id): ?User
    {
        return $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->find($id);
    }
    /**
     * Try to find commodity favorite for current user.
     *
     * @param   Commodity $commodity        Commodity.
     *
     * @return  CommodityFavorite|null      Favorite if any.
     */
    private function findCommodityFavorite(Commodity $commodity): ?CommodityFavorite
    {
        return $this
            ->getDoctrine()
            ->getRepository(CommodityFavorite::class)
            ->findOneBy([
                'commodity' => $commodity,
                'user'      => $this->user,
            ]);
    }
    /**
     * Try to find user favorite for current user.
     *
     * @param   User $user                  User.
     *
     * @return  UserFavorite|null           Favorite if any.
     */
    private function findUserFavorite(User $user): ?UserFavorite
    {
        return $this
            ->getDoctrine()
            ->getRepository(UserFavorite::class)
            ->findOneBy([
                'userFavorite'  => $user,
                'user'          => $this->user,
            ]);
    }
    /**
     * Create new commodity favorite.
     *
     * @param   Commodity $commodity        Commodity.
     *
     * @return  CommodityFavorite           New favorite.
     */
    private function createCommodityFavorite(Commodity $commodity): CommodityFavorite
    {
        return (new CommodityFavorite())
            ->setUser($this->user)
            ->setCommodity($commodity);
    }
    /**
     * Create new user favorite.
     *
     * @param   User $user                  User.
     *
     * @return  UserFavorite                New favorite.
     */
    private function createUserFavorite(User $user): UserFavorite
    {
        return (new UserFavorite())
            ->setUser($this->user)
            ->setUserFavorite($user);
    }
}
