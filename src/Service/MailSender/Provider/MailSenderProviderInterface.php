<?php

namespace App\Service\MailSender\Provider;

interface MailSenderProviderInterface
{
    /**
     * Send email.
     *
     * @param   string  $email              Email.
     * @param   string  $title              Email title.
     * @param   string  $template           Template path (relative, from templates directory root)
     * @param   array   $data               Template data.
     *
     * @return  bool                        Success.
     */
    public function send(string $email, string $title, string $template, array $data = []): bool;
}
