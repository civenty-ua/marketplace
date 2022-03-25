<?php

namespace App\Service\SmsSender\Provider;

interface SmsSenderProviderInterface
{
    public function send(array $phones, string $message): bool;
}
