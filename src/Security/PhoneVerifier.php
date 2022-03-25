<?php

namespace App\Security;

class PhoneVerifier
{



    /**
     * @param $url
     * @param $jsonValue
     * @param $user
     * @param $password
     * @return array
     */
    function sendRequestToSputnik($url, $jsonValue, $user, $password) :array {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($jsonValue));
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_USERPWD, $user.':'.$password);
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
