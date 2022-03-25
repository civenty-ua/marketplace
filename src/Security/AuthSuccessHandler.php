<?php

namespace App\Security;

use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\{
    HttpUtils,
    Authentication\DefaultAuthenticationSuccessHandler,
};
use App\Entity\User;
/**
 * Authorization success handler.
 */
class AuthSuccessHandler extends DefaultAuthenticationSuccessHandler
{
    private TranslatorInterface     $translator;
    private UrlGeneratorInterface   $urlGenerator;
    /**
     * Constructor.
     *
     * @param   HttpUtils               $httpUtils
     * @param   array                   $options
     * @param   TranslatorInterface     $translator
     * @param   UrlGeneratorInterface   $urlGenerator
     */
    public function __construct(
        HttpUtils               $httpUtils,
        array                   $options    = [],
        TranslatorInterface     $translator,
        UrlGeneratorInterface   $urlGenerator
    ) {
        parent::__construct($httpUtils, $options);
        $this->translator   = $translator;
        $this->urlGenerator = $urlGenerator;
    }
    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        /** @var User $user */
        $user = $token->getUser();
        if (!$user->isVerified()) {
            $message                = $this->translator->trans('form_registration.confirm_email_error');
            $confirmEmailLinkTitle  = $this->translator->trans('form_registration.confirm_email_link');
            $confirmEmailLinkUrl    = $this->urlGenerator->generate('app_email_confirmation_resend', [
                'email' => $user->getEmail(),
            ]);
            $confirmEmailLink       = "<a href=\"$confirmEmailLinkUrl\">$confirmEmailLinkTitle</a>";

            throw new CustomUserMessageAuthenticationException(implode('<br>', [
                $message,
                $confirmEmailLink,
            ]));
        }

        $this->setOptions(array_merge($this->getOptions(), [
            'default_target_path' => $this->httpUtils->generateUri($request, 'app_profile')
        ]));
        return parent::onAuthenticationSuccess($request, $token);
    }
}
