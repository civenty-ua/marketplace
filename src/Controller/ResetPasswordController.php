<?php

namespace App\Controller;

use Symfony\{Bundle\FrameworkBundle\Controller\AbstractController,
    Component\Mailer\Exception\TransportExceptionInterface,
    Component\Routing\Annotation\Route,
    Component\HttpFoundation\Request,
    Component\HttpFoundation\Response,
    Component\HttpFoundation\RedirectResponse,
    Component\Security\Core\Encoder\UserPasswordEncoderInterface,
    Component\Mailer\MailerInterface,
    Component\Mime\Address,
    Bridge\Twig\Mime\TemplatedEmail,
    Contracts\Translation\TranslatorInterface};
use App\{
    Entity\User,
    Form\ChangePasswordFormType,
    Form\ResetPasswordRequestFormType,
};
use SymfonyCasts\{
    Bundle\ResetPassword\Controller\ResetPasswordControllerTrait,
    Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface,
    Bundle\ResetPassword\ResetPasswordHelperInterface,
};
use Psr\Log\LoggerInterface;

/**
 * @Route("/reset-password")
 */
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    private $resetPasswordHelper;
    private TranslatorInterface $translator;
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(
        ResetPasswordHelperInterface $resetPasswordHelper,
        TranslatorInterface $translator,
        MailerInterface $mailer,
        LoggerInterface $logger
    )
    {
        $this->resetPasswordHelper = $resetPasswordHelper;
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("", name="app_forgot_password_request")
     */
    public function request(Request $request): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('success', $this->translator->trans('reset_password_email.success'));
            $this->logger->info("FORM SUCCESS");
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData()
            );
        }
        $this->logger->info("FORM Error", [$form->isSubmitted()]);


        return $this->render('reset_password/request.html.twig',
            [
                'requestForm' => $form->createView(),
            ]);
    }

    /**
     * Display & process form to request a password reset.
     *
     * @Route("/sendemail", name="app_forgot_password_request_1")
     */
    public function sendemail(Request $request): Response
    {
        $this->logger->error('Start send email');
        $email = (new TemplatedEmail())
            ->from(new Address($this->getParameter('email.info'), 'Agro Portal Bot'))
            ->to('purikv@gmail.com')
            ->subject($this->translator->trans('reset_password_email.subject'))
            ->html('test');

        try {
            $this->mailer->send($email);
            $this->logger->error('Email send success');
        } catch (\Exception $exception) {
            $this->logger->error('ERROR SEND EMAIL: ', [$exception->getMessage()]);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('ERROR SEND EMAIL: ', [$e->getMessage()]);
        }

        return $this->redirectToRoute('app_forgot_password_request');
    }

    /**
     * Confirmation page after a user has requested a password reset.
     *
     * @Route("/check-email", name="app_check_email")
     */
    public function checkEmail(): Response
    {
        // We prevent users from directly accessing this page
        if (null === ($resetToken = $this->getTokenObjectFromSession())) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->render('reset_password/check_email.html.twig',
            [
                'resetToken' => $resetToken,
            ]);
    }

    /**
     * Validates and process the reset URL that the user clicked in their email.
     *
     * @Route("/reset/{token}", name="app_reset_password")
     */
    public function reset(Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        string $token = null
    ): Response {
        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);
        } catch (ResetPasswordExceptionInterface $e) {
            $this->addFlash('reset_password_error',
                    $this->translator->trans('form_reset.link_expired')
                );

            return $this->redirectToRoute('app_forgot_password_request');
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.
            $this->resetPasswordHelper->removeResetRequest($token);

            // Encode the plain password, and set it.
            $encodedPassword = $passwordEncoder->encodePassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->getDoctrine()->getManager()->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            return $this->redirectToRoute('home');
        }

        return $this->render('reset_password/reset.html.twig',
            [
                'resetForm' => $form->createView(),
            ]);
    }
    private function sendErrorEmail($emailFormData)
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->getParameter('email.info'), 'Agro Portal Bot'))
            ->to($emailFormData)
            ->subject($this->translator->trans('reset_password_email.subject'))
            ->htmlTemplate('reset_password/error_email.html.twig');

        $this->mailer->send($email);
    }

    private function processSendingPasswordResetEmail(
        string $emailFormData
    ): RedirectResponse {
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy([
            'email' => $emailFormData,
        ]);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            $this->sendErrorEmail($emailFormData);
            return $this->redirectToRoute('app_check_email');
        }
        $this->logger->info('Send email to user id: ', [$user->getId()]);
        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            // If you want to tell the user why a reset email was not sent, uncomment
            // the lines below and change the redirect to 'app_forgot_password_request'.
            // Caution: This may reveal if a user is registered or not.
            //
            // $this->addFlash('reset_password_error', sprintf(
            //     'There was a problem handling your password reset request - %s',
            //     $e->getReason()
            // ));

            return $this->redirectToRoute('app_check_email');
        }

        $email = (new TemplatedEmail())
            ->from(new Address($this->getParameter('email.info'), 'Agro Portal Bot'))
            ->to($user->getEmail())
            ->subject($this->translator->trans('reset_password_email.subject'))
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        try {
            $this->mailer->send($email);
            $this->logger->info('Email send success');
        } catch (\Exception $exception) {
            $this->logger->error('ERROR SEND EMAIL: ', [$exception->getMessage()]);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('ERROR SEND EMAIL: ', [$e->getMessage()]);
        }

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
