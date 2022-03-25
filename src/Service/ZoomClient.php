<?php


namespace App\Service;


use Symfony\Component\DependencyInjection\ContainerInterface;

class ZoomClient
{

    private $errors;
    private $apiKey;
    private $apiSecret;
    private $baseUrl;
    private $timeout;

    /**
     * Constructor.
     *
     * @param ContainerInterface $container Application container
     */

    public function __construct(ContainerInterface $container,$options = array())
    {
        // Store any options if they map to valid properties
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) $this->$key = $value;
        }
        if (is_null($this->apiKey) && is_null($this->apiSecret)) {

            $this->apiKey = $container->getParameter('app.zoom.api_key');

            $this->apiSecret = $container->getParameter('app.zoom.client_secret');
        }

//        dd($this->apiKey);
        $this->baseUrl = 'https://api.zoom.us/v2';
        $this->timeout = 30;



    }

    static function urlsafeB64Encode($string)
    {
        return str_replace('=', '', strtr(base64_encode($string), '+/', '-_'));
    }

    private function generateJWT()
    {
        $token = array(
            'iss' => $this->apiKey,
            'exp' => time() + 60,
        );
        $header = array(
            'typ' => 'JWT',
            'alg' => 'HS256',
        );

        $toSign =
            self::urlsafeB64Encode(json_encode($header))
            . '.' .
            self::urlsafeB64Encode(json_encode($token));

        $signature = hash_hmac('SHA256', $toSign, $this->apiSecret, true);

        return $toSign . '.' . self::urlsafeB64Encode($signature);
    }

    private function headers()
    {
        return array(
            'Authorization: Bearer ' . $this->generateJWT(),
            'Content-Type: application/json',
            'Accept: application/json',
        );
    }

    private function pathReplace($path, $requestParams)
    {
        $errors = array();
        $path = preg_replace_callback('/\\{(.*?)\\}/', function ($matches) use ($requestParams, $errors) {
            if (!isset($requestParams[$matches[1]])) {
                $this->errors[] = 'Required path parameter was not specified: ' . $matches[1];
                return '';
            }
            return rawurlencode($requestParams[$matches[1]]);
        }, $path);

        if (count($errors)) $this->errors = array_merge($this->errors, $errors);
        return $path;
    }

    public function doRequest($method, $path, $queryParams = array(), $pathParams = array(), $body = '')
    {

        if (is_array($body)) {
            // Treat an empty array in the body data as if no body data was set
            if (!count($body)) $body = '';
            else $body = json_encode($body);
        }

        $this->errors = array();
        $this->responseCode = 0;

        $path = $this->pathReplace($path, $pathParams);

        if (count($this->errors)) return false;

        $method = strtoupper($method);
        $url = $this->baseUrl . $path;

        // Add on any query parameters
        if (count($queryParams)) $url .= '?' . http_build_query($queryParams);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers());
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (in_array($method, array('DELETE', 'PATCH', 'POST', 'PUT'))) {

            // All except DELETE can have a payload in the body
            if ($method != 'DELETE' && strlen($body)) {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

        $result = curl_exec($ch);

        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        $this->responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return json_decode($result, true);
    }

    // Returns the errors responseCode returned from the last call to doRequest
    function requestErrors()
    {
        return $this->errors;
    }

    // Returns the responseCode returned from the last call to doRequest
    function responseCode()
    {
        return $this->responseCode;
    }
}