<?php

namespace App\Event\Commodity\Profile\Certificate;

use App\Entity\Market\UserCertificate;

class CertificateCreateEvent extends \Symfony\Contracts\EventDispatcher\Event
{
    private $UserCertificate;

    /**
     * @return UserCertificate
     */
    public function getUserCertificate(): UserCertificate
    {
        return $this->UserCertificate;
    }

    /**
     * @param UserCertificate $UserCertificate
     */
    public function setUserCertificate(UserCertificate $UserCertificate): void
    {
        $this->UserCertificate = $UserCertificate;
    }
}