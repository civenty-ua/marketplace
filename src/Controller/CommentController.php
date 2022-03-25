<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\{
    Request,
    Response,
    JsonResponse,
    Session\SessionInterface
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\{
    Item,
    Comment,
    User,
};
use App\Form\CommentType;

class CommentController extends AbstractController
{
    /**
     * @Route("/comment/{itemId}/post", name="comment_post")
     */
    public function post(
        Request             $request,
        SessionInterface    $session,
        ?UserInterface      $user,
        int                 $itemId
    ): Response {
        $comment    = new Comment();
        $form       = $this->createForm(CommentType::class, $comment);

        $form->handleRequest($request);
        if (!$form->isSubmitted() || !$form->isValid()) {
            return new JsonResponse([
                'message' => 'form validation error',
            ], 400);
        }

        /** @var Item $item */
        $item = $this
            ->getDoctrine()
            ->getRepository(Item::class)
            ->findOneBy(['id' => $itemId]);
        if (!$item) {
            return new JsonResponse([
                'message' => "item $itemId was not found",
            ], 400);
        }
        $comment->setItem($item);

        /** @var User|null $user */
        if ($user) {
            $comment->setAuthorizedUser($user);
        } else {
            $comment->setAnonymousUser($session->getId());
        }

        $message = (string) $form->get('message')->getData();
        if (strlen($message) === 0) {
            return new JsonResponse([
                'message' => 'message is empty',
            ], 400);
        }
        $comment->setMessage($message);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($comment);
        $entityManager->flush();

        return new JsonResponse($this->parseCommentToArray($comment), 200);
    }
    /**
     * @Route("/comment/{itemId}/get/list", name="comment_get_list")
     */
    public function getList(Request $request, int $itemId): Response
    {
        $item = $this
            ->getDoctrine()
            ->getRepository(Item::class)
            ->findOneBy(['id' => $itemId]);
        if (!$item) {
            return new JsonResponse([
                'message' => "item $itemId was not found",
            ], 400);
        }

        /** @var Comment[] $comments */
        $limit          = $request->query->get('limit')     ?? 10;
        $offset         = $request->query->get('offset')    ?? 0;
        $comments       = $this
            ->getDoctrine()
            ->getRepository(Comment::class)
            ->findBy(
                [
                    'item' => $item->getId(),
                ],
                ['createdAt' => 'DESC'],
                $limit,
                $offset
            );
        $commentsData   = [];

        foreach ($comments as $comment) {
            $commentsData[] = $this->parseCommentToArray($comment);
        }

        return new JsonResponse([
            'comments' => $commentsData,
        ], 200);
    }
    /**
     * Parse comment entity to array data.
     *
     * @param   Comment $comment            Comment.
     *
     * @return  array                       Comment data.
     */
    private function parseCommentToArray(Comment $comment): array
    {
        return [
            'createdAt'     => $comment->getCreatedAt()->format('d.m.Y H:i:s'),
            'userAvatar'    => $comment->getAuthorizedUser()
                ? $comment->getAuthorizedUser()->getAvatar()
                : '',
            'userTitle'     => $comment->getUserTitle(),
            'message'       => $comment->getMessage(),
        ];
    }
}
