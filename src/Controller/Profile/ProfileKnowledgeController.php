<?php

namespace App\Controller\Profile;

use RuntimeException;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    Response,
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\Profile\{
    ChangePasswordFormType,
    ProfileEditFormType,
};
use App\Service\UserHistoryManager;
use App\Service\FileManager\{
    FileManagerInterface,
    FileManager,
};
use App\Controller\AuthRequiredControllerInterface;
use App\Entity\{
    User,
    UserDownload,
    UserViewed,
};
/**
 * Class ProfileKnowledgeController
 * @package App\Controller\Profile
 */
class ProfileKnowledgeController extends AbstractController implements AuthRequiredControllerInterface
{
    private FileManagerInterface $fileManager;
    private TranslatorInterface $translator;

    public function __construct(
        FileManagerInterface         $fileManager,
        TranslatorInterface          $translator
    )
    {
        $this->fileManager = $fileManager;
        $this->translator = $translator;
    }

    /**
     * @Route("/profile/knowledge", name="app_profile_knowledge")
     */
    public function profileKnowledge(Request $request, ?UserInterface $user): Response
    {
        /** @var User|null $user */
        $profileForm = $this->createForm(ProfileEditFormType::class, $user);
        $changePasswordForm = $this->createForm(ChangePasswordFormType::class, $user);
        $entityManager = $this->getDoctrine()->getManager();

        $profileForm->handleRequest($request);
        if ($profileForm->isSubmitted()) {
            try {
                $this->handleProfileFormPost($profileForm, $user);
                return $this->redirectToRoute('app_profile');
            } catch (RuntimeException $exception) {

            }
        }

        $changePasswordForm->handleRequest($request);
        if ($changePasswordForm->isSubmitted()) {
            try {
                $this->handleChangePasswordFormPost($changePasswordForm, $user);
                return $this->redirectToRoute('app_profile');
            } catch (RuntimeException $exception) {

            }
        }

        return $this->render('profile/profile-knowledge.html.twig', [
            'user' => $this->getUser(),
            'downloadedFiles' => $entityManager
                ->getRepository(UserDownload::class)
                ->findBy(['user' => $user]),
            'viewedWebinars' => $entityManager
                ->getRepository(UserViewed::class)
                ->getViewedWebinarsByUser($user),
            'viewedCourses' => $entityManager
                ->getRepository(UserViewed::class)
                ->getViewedCoursesByUser($user),
            'profileEditForm' => $profileForm
                ->createView(),
            'changePasswordForm' => $changePasswordForm
                ->createView(),
        ]);
    }



    /**
     * @Route("/profile/download-file", name="app_profile_download_file")
     */
    public function profileSaveFile(
        Request            $request,
        ?UserInterface     $user,
        UserHistoryManager $userHistoryManager
    )
    {
        $result = ['success' => false];

        $link = FileManager::getFullLink($request->request->get('link'));
        $text = $request->request->get('text');

        if ($userHistoryManager->saveFile($user, $link, $text)) {
            $result = ['success' => true];
        }

        return new JsonResponse($result);
    }

    /**
     * @Route("/profile/files/{id}", name="app_profile_file")
     */
    public function profileGetFile(
        ?UserInterface $user,
        FileManager    $fileManager,
        int            $id
    )
    {
        /** @var UserDownload $file */
        $file = $this
            ->getDoctrine()
            ->getManager()
            ->getRepository(UserDownload::class)
            ->findOneBy([
                'id' => $id,
                'user' => $user,
            ]);

        if (!$file) {
            throw new NotFoundHttpException();
        }

        $fileManager->downloadUserFile($file);

        return $this->redirect('/profile');
    }
}
