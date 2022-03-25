<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\{
    Request,
    Response,
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpKernel\Exception\{
    ConflictHttpException,
    UnauthorizedHttpException,
    UnprocessableEntityHttpException,
    NotFoundHttpException,
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\{
    Item,
    User,
    UserItemFeedback,
    UserItemFeedbackAnswer,
};
use App\Form\Feedback\UserFeedbackType;

class FeedbackFormController extends AbstractController
{
    protected Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }
    /**
     * @Route("feedback/{slug}/form", name="item_feedback_form")
     */
    public function form(string $slug): Response
    {
        try {
            $user               = $this->findUser();
            $item               = $this->findItem($slug);
            $this->checkUserFeedbackNotExist($user, $item);
            $userNewFeedback    = $this->buildNewUserFeedback($user, $item);
            $userFeedbackForm   = $this->createForm(
                UserFeedbackType::class,
                $userNewFeedback,
                [
                    'action' => $this->generateUrl('item_feedback_post', [
                        'slug' => $slug,
                    ]),
                ]
            );
        } catch (UnauthorizedHttpException $exception) {
            return $this->redirectToRoute('login');
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException("item $slug form was not found");
        } catch (UnprocessableEntityHttpException $exception) {
            throw new UnprocessableEntityHttpException("item $slug has no feedback form");
        } catch (ConflictHttpException $exception) {
            return $this->redirectToRoute('item_feedback_success', [
                'slug' => $slug,
            ]);
        }

        return $this->render('feedback-form/form.html.twig', [
            'item'  => $item,
            'form'  => $userFeedbackForm->createView(),
        ]);
    }
    /**
     * @Route("/feedback/{slug}/post", name="item_feedback_post")
     */
    public function post(Request $request, string $slug): Response
    {
        try {
            $user               = $this->findUser();
            $item               = $this->findItem($slug);
            $this->checkUserFeedbackNotExist($user, $item);
            $userNewFeedback    = $this->buildNewUserFeedback($user, $item);
            $userFeedbackForm   = $this->createForm(UserFeedbackType::class, $userNewFeedback);
            $userFeedbackForm->handleRequest($request);
        } catch (UnauthorizedHttpException $exception) {
            return $this->redirectToRoute('login');
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException("item $slug form was not found");
        } catch (UnprocessableEntityHttpException $exception) {
            throw new UnprocessableEntityHttpException("item $slug has no feedback form");
        } catch (ConflictHttpException $exception) {
            return $this->redirectToRoute('item_feedback_success', [
                'slug' => $slug,
            ]);
        }

        if (!$userFeedbackForm->isSubmitted() || !$userFeedbackForm->isValid()) {
            return $this->render('feedback-form/form.html.twig', [
                'form' => $userFeedbackForm->createView(),
            ]);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($userNewFeedback);
        $entityManager->flush();

        return $this->redirectToRoute('item_feedback_success', [
            'slug' => $slug,
        ]);
    }
    /**
     * @Route("/feedback/{slug}/success", name="item_feedback_success")
     */
    public function success(string $slug): Response
    {
        try {
            $user   = $this->findUser();
            $item   = $this->findItem($slug);
        } catch (UnauthorizedHttpException $exception) {
            return $this->redirectToRoute('login');
        } catch (NotFoundHttpException $exception) {
            throw new NotFoundHttpException("item $slug form was not found");
        }

        try {
            $this->checkUserFeedbackNotExist($user, $item);
            return $this->redirectToRoute('item_feedback_form', [
                'slug' => $slug,
            ]);
        } catch (ConflictHttpException $exception) {
            return $this->render('feedback-form/success.html.twig', [
                'item' => $item,
            ]);
        }
    }
    /**
     * Try to find current user.
     *
     * @return  User                            User.
     * @throws  UnauthorizedHttpException       User was not found.
     */
    private function findUser(): User
    {
        /** @var User|null $user */
        $user = $this->security->getUser();
        if (!$user){
            throw new UnauthorizedHttpException('');
        }

        return $user;
    }
    /**
     * Try to find item.
     *
     * @param   string $slug                        Item slug.
     *
     * @return  Item                                Item.
     * @throws  NotFoundHttpException               Item was not found.
     * @throws  UnprocessableEntityHttpException    Item has no feedback form
     */
    private function findItem(string $slug): Item
    {
        /** @var Item|null $item */
        $item = $this
            ->getDoctrine()
            ->getRepository(Item::class)
            ->findOneBy([
                'slug' => $slug,
            ]);
        if (!$item){
            throw new NotFoundHttpException();
        }
        if (!$item->getFeedbackForm()){
            throw new UnprocessableEntityHttpException();
        }

        return $item;
    }
    /**
     * Check user does not already have feedback on this form.
     *
     * @param   User    $user                   User.
     * @param   Item    $item                   Item.
     *
     * @return  void
     * @throws  ConflictHttpException           User already has feedback.
     */
    private function checkUserFeedbackNotExist(User $user, Item $item): void
    {
        /** @var UserItemFeedback|null $userFeedback */
        $userFeedbackExist = $this
            ->getDoctrine()
            ->getRepository(UserItemFeedback::class)
            ->findOneBy([
                'user'          => $user->getId(),
                'item'          => $item->getId(),
                'feedbackForm'  => $item->getFeedbackForm()->getId(),
            ]);
        if ($userFeedbackExist){
            throw new ConflictHttpException();
        }
    }
    /**
     * Build new UserFeedback entity.
     *
     * @param   User    $user                   User.
     * @param   Item    $item                   Item.
     *
     * @return  UserItemFeedback                UserFeedback prepared entity.
     */
    private function buildNewUserFeedback(User $user, Item $item): UserItemFeedback
    {
        $userFeedback           = (new UserItemFeedback())
            ->setUser($user)
            ->setItem($item)
            ->setFeedbackForm($item->getFeedbackForm());
        $feedbackFormQuestions  = [];

        foreach ($item->getFeedbackForm()->getFeedbackFormQuestions() as $question) {
            $questionOrder = $question->getSort();

            while (isset($feedbackFormQuestions[$questionOrder])) {
                $questionOrder++;
            }

            $feedbackFormQuestions[$questionOrder] = $question;
        }

        ksort($feedbackFormQuestions);
        foreach ($feedbackFormQuestions as $question) {
            $userAnswer = (new UserItemFeedbackAnswer())
                ->setUserFeedback($userFeedback)
                ->setFeedbackFormQuestion($question);
            $userFeedback->addUserFeedbackAnswer($userAnswer);
        }

        return $userFeedback;
    }
}
