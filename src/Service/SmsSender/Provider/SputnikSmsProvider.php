<?php

namespace App\Service\SmsSender\Provider;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SputnikSmsProvider implements SmsSenderProviderInterface
{
    const API_LINK = 'https://esputnik.com/api/v1/message/sms';

    private ParameterBagInterface $parameterBag;
    private LoggerInterface $logger;
    private string $apiUser;
    private string $apiPassword;

    public function __construct(ParameterBagInterface $parameterBag, LoggerInterface $logger)
    {
        $this->parameterBag = $parameterBag;
        $this->logger = $logger;
        $this->apiUser = $this->parameterBag->get('sputnik.user');
        $this->apiPassword = $this->parameterBag->get('sputnik.password');
    }

    public function send(array $phones, string $message): bool
    {
        $jsonObject = $this->buildJsonObject($phones, $message);
        $request = $this->sendRequestToSputnik($jsonObject);
        if (!$request['status']) {
            $this->logger->error('SputnikSmsProvider: ' . $request['message']);
            return false;
        }

        return true;
    }

    private function buildJsonObject(array $phones, string $message)
    {
        $jsonObject = new \stdClass();
        $jsonObject->text = $message;
        $jsonObject->from = $this->parameterBag->get('sputnik.interface');
        $jsonObject->phoneNumbers = $phones;

        return $jsonObject;
    }

    /**
     * @param $url
     * @param $jsonObject
     * @param $user
     * @param $password
     * @return array
     */
    private function sendRequestToSputnik($jsonObject): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonObject));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, self::API_LINK);
        curl_setopt($ch,CURLOPT_USERPWD, $this->apiUser . ':' . $this->apiPassword);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_SSLVERSION, 6);
        $output = curl_exec($ch);
        curl_close($ch);

        list($header, $body) = explode("\r\n\r\n", $output, 2);
        $response = json_decode($body, true);
        if (isset($response['results']['status']) and $response['results']['status'] == 'OK') {
            return ['status' => true];
        } else {
            if (isset($response['results']['message'])) {
                return ['status' => false, 'message' => $response['results']['message']];
            } else {
                return ['status' => false, 'message' => 'Unknown error'];
            }
        }
    }
}
