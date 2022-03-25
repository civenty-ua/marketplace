<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\{
    JsonResponse,
    Request,
    Response,
};
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\{
    Encoder\UserPasswordEncoderInterface,
    User\UserInterface,
};
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormError;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use App\Helper\PhoneHelper;
use App\Service\SmsSender\Provider\SputnikSmsProvider;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Entity\User;
/**
 * Registration controller.
 */
class RegistrationController extends AbstractController
{
    private EmailVerifier           $emailVerifier;
    private TranslatorInterface     $translator;
    private TransportInterface     $transport;

    public function __construct(
        EmailVerifier       $emailVerifier,
        TranslatorInterface $translator,
        TransportInterface $transport
    ) {
        $this->emailVerifier    = $emailVerifier;
        $this->translator       = $translator;
        $this->transport       = $transport;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(
        Request                      $request,
        ?UserInterface               $user,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        if ($user) {
            return $this->redirectToRoute('app_profile');
        }

        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $phone      = $form->get('phone')->getData();
            $index      = User::getPhoneVerifyCodeSessionIndex($phone);
            $codeRight  = $this->get('session')->get($index);

            if ($form->get('code')->getData() == $codeRight) {
                $user->setVerifyPhone(true);
            } else {
                $errorMessage = $this->translator->trans('form_registration.code_invalid');
                $form->get('phone')->addError(new FormError($errorMessage));
            }

            if ($form->get('plainPassword')->getData() !== $form->get('passwordConfirm')->getData()) {
                $errorMessage = $this->translator->trans('form_registration.password_confirm_error');
                $form->get('passwordConfirm')->addError(new FormError($errorMessage));
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $this->processEmailConfirmationSending($user);
            $this->addFlash('success', $this->translator->trans('success_registration.confirm_div'));

            return $this->redirectToRoute('home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(
        Request         $request,
        UserRepository  $userRepository
    ): Response
    {
        $id = $request->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        $this->addFlash('success', $this->translator->trans('form_registration.email_verify'));

        // IMPORTANT: remove back URL data in session.
        // Should find normal approach to remove it for email confirmation route
        $request->getSession()->remove('_security.main.target_path');

        return $this->redirectToRoute('login');
    }

    /**
     * @Route("/verify/send-code", name="verify_send_code", methods={"POST"})
     *
     * @param Request $request
     * @param SputnikSmsProvider $sputnikSmsProvider
     *
     * @return JsonResponse
     */
    public function verifySendCode(Request $request, SputnikSmsProvider $sputnikSmsProvider): JsonResponse
    {
        $phone = $request->request->get('phone', '');
        $verificationCode = rand(100000, 999999);
        $msg = $this->translator->trans('form_registration.sms') . $verificationCode;

        $index = User::getPhoneVerifyCodeSessionIndex($phone);
        $this->get('session')->set($index, $verificationCode);
        $phones = PhoneHelper::getPhonesArray($phone);

        if ($sputnikSmsProvider->send($phones, $msg)) {
            return new JsonResponse([], Response::HTTP_OK);
        }

        return new JsonResponse([], Response::HTTP_BAD_REQUEST);
    }
    /**
     * @Route("/confirm/email/resend", name="app_email_confirmation_resend")
     *
     * @param   Request $request            Request.
     *
     * @return  Response                    Response.
     */
    public function resendEmailConfirmationLetter(Request $request): Response
    {
        /** @var User|null $user */
        $email  = $request->query->get('email');
        $user   = $this
            ->getDoctrine()
            ->getRepository(User::class)
            ->findOneBy([
                'email' => $email,
            ]);

        if (!$user) {
            $this->addFlash('error', $this->translator->trans('form_registration.unknown_email_error'));

            return $this->redirectToRoute('home');
        }

        $this->processEmailConfirmationSending($user);
        $this->addFlash('success', $this->translator->trans('success_registration.confirm_div'));

        return $this->redirectToRoute('home');
    }
    /**
     * Send email confirmation to user.
     *
     * @param   User $user
     *
     * @return  void
     */
    private function processEmailConfirmationSending(User $user): void
    {
        $this->emailVerifier->sendEmailConfirmation(
            'app_verify_email',
            $user,
            (new TemplatedEmail())
                ->from(new Address($this->getParameter('email.info'), $this->getParameter('email.title')))
                ->to($user->getEmail())
                ->subject($this->translator->trans('form_registration.confirm_email'))
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }
}
