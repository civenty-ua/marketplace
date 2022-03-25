<?php
declare(strict_types=1);

namespace App\Event;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Karser\Recaptcha3Bundle\Validator\Constraints\Recaptcha3Validator;
use App\Form\LoginForm;

class AuthListener extends UsernamePasswordFormAuthenticationListener
{
    private ContainerInterface  $container;
    private Recaptcha3Validator $recaptcha3Validator;
    private TranslatorInterface $translator;

    public function setContainer(
        ContainerInterface $container,
        Recaptcha3Validator $recaptcha3Validator,
        TranslatorInterface $translator
    ) {
        $this->container = $container;
        $this->recaptcha3Validator = $recaptcha3Validator;
        $this->translator = $translator;

        return $this;
    }

    public function attemptAuthentication(Request $request)
    {
        /* @var $form Form */
        $form = $this->container->get('form.factory')->create(LoginForm::class);
        $form->handleRequest($request);

        $recaptchaResponse = $this->recaptcha3Validator->getLastResponse();
        if (!$recaptchaResponse) {
            $error = $this->translator->trans('form_registration.captcha_connection_failed');
            throw new CustomUserMessageAuthenticationException($error);
        }
        if ($recaptchaResponse->getScore() < 0.7) {
            $error = $this->translator->trans('form_registration.captcha');
            throw new CustomUserMessageAuthenticationException($error);
        }

        return parent::attemptAuthentication($request);
    }
}
