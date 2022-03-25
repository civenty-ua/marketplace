<?php

namespace App\Security;

use Symfony\Component\Security\Http\Authentication\DefaultAuthenticationFailureHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthFailureHandler extends DefaultAuthenticationFailureHandler
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $session = $request->getSession();
        $session->set('auth_fails', $session->get('auth_fails', 0) + 1);

        return parent::onAuthenticationFailure($request, $exception);
    }
}