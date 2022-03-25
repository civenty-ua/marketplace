<?php

namespace App\Controller;

use App\Form\LoginForm;
use Symfony\{
    Bundle\FrameworkBundle\Controller\AbstractController,
    Component\Routing\Annotation\Route,
    Component\HttpFoundation\Request,
    Component\HttpFoundation\Response,
    Component\Security\Http\Authentication\AuthenticationUtils,
};
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;

/**
 * Class SecurityController
 * @package App\Controller
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     */
    public function login(AuthenticationUtils $authenticationUtils,
        Request $request,
        Recaptcha3Validator $recaptcha3Validator
    ): Response {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->createForm(LoginForm::class, [
            'lastUsername' => $lastUsername,
            'error' => $error,
        ]);

        $form->get('_username')->setData($lastUsername);
        $response = $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 'error' => $error,
            'loginForm' => $form->createView(),
        ]);
        $form->handleRequest($request);

        return $response;
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
