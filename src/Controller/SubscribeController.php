<?php

namespace App\Controller;

use Symfony\{
    Bundle\FrameworkBundle\Controller\AbstractController,
    Component\Routing\Annotation\Route,
    Component\HttpFoundation\Response,
    Component\Security\Core\User\UserInterface,
};
use App\Entity\User;

/**
 * Class SubscribeController
 * @package App\Controller
 */
class SubscribeController extends AbstractController
{
    /**
     * @Route("/subscribe/post", name="subscribe_post")
     *
     * @param UserInterface|null $user
     *
     * @return Response
     */
    public function post(?UserInterface $user): Response
    {
        /** @var User|null $user */

        if (!$user) {
            return (new Response())->setStatusCode(500);
        }

        $user->setIsNewsSub(true);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return (new Response())->setStatusCode(200);
    }
}
