<?php

namespace App\Controller\Profile;

use RuntimeException;
use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    Response,
};
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\{
    FormInterface,
    FormError,
};
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Form\Profile\{
    ChangePasswordFormType,
    ProfileEditFormType,
};
use App\Service\FileManager\{
    FileManager,
    Mapping\UserAvatarMapping,
};
use App\Controller\AuthRequiredControllerInterface;
use App\Entity\{
    User,
    UserDownload,
    UserViewed,
};
/**
 * Class ProfileController
 * @package App\Controller\Profile
 */
class ProfileController extends AbstractController implements AuthRequiredControllerInterface
{
    private TranslatorInterface $translator;
    private UserPasswordEncoderInterface $passwordEncoder;
    private FileManager $fileManager;

    public function __construct(
        TranslatorInterface          $translator,
        UserPasswordEncoderInterface $passwordEncoder,
        FileManager $fileManager
    )
    {
        $this->fileManager = $fileManager;
        $this->translator = $translator;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/profile", name="app_profile")
     */
    public function profile(Request $request, ?UserInterface $user): Response
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

        return $this->render('profile/profile.html.twig', [
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
     * Handle profile form post.
     *
     * @param FormInterface $form Form.
     * @param User $user User.
     *
     * @return  void
     * @throws  RuntimeException            Any error.
     */
    private function handleProfileFormPost(FormInterface $form, User $user): void
    {
        if (!$user->getVerifyPhone()) {
            $phone = $form->get('phone')->getData();
            $index = User::getPhoneVerifyCodeSessionIndex($phone);
            $codeRight = $this->get('session')->get($index);

            if ($form->get('code')->getData() == $codeRight) {
                $user->setVerifyPhone(true);
            } else {
                $errorMessage = $this->translator->trans('form_registration.code_invalid');
                $form->get('phone')->addError(new FormError($errorMessage));
            }
        }

        if (!$form->isValid()) {
            throw new RuntimeException();
        }

        /** @var UploadedFile|null $avatar */
        $avatar = $form->get('avatar')->getData();

        if ($avatar instanceof UploadedFile) {
            $avatarFileName = $this->fileManager->uploadMappedFile($avatar, UserAvatarMapping::class);
            $user->setAvatar($avatarFileName);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
    }

    /**
     * Handle change password form post.
     *
     * @param FormInterface $form Form.
     * @param User $user User.
     *
     * @return  void
     * @throws  RuntimeException            Any error.
     */
    private function handleChangePasswordFormPost(FormInterface $form, User $user): void
    {
        if (!$this->passwordEncoder->isPasswordValid($user, $form->get('oldPassword')->getData())) {
            $errorMessage = $this->translator->trans('form_registration.password_incorrect');
            $form->get('oldPassword')->addError(new FormError($errorMessage));
        }
        if ($form->get('plainPassword')->getData() !== $form->get('passwordConfirm')->getData()) {
            $errorMessage = $this->translator->trans('form_registration.password_confirm_error');
            $form->get('passwordConfirm')->addError(new FormError($errorMessage));
        }

        if (!$form->isValid()) {
            throw new RuntimeException();
        }

        $user->setPassword(
            $this->passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            )
        );

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();
    }

}
