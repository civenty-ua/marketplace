<?php
declare(strict_types=1);

namespace App\Service;

use RuntimeException;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
/**
 * Class YoutubeClient.
 *
 * @package App\Service
 */
class YoutubeClient
{
    private const API_URI = 'https://www.googleapis.com/youtube/v3';

    private string              $googleApiKey;
    private HttpClientInterface $httpClient;
    /**
     * Constructor.
     *
     * @param ContainerInterface    $container      Application container
     * @param HttpClientInterface   $httpClient     HTTP client
     */
    public function __construct(
        ContainerInterface  $container,
        HttpClientInterface $httpClient
    ) {
        $this->googleApiKey = (string) $container->getParameter('app.google.api_key');
        $this->httpClient   = $httpClient;
    }
    /**
     * Read video data and provide it.
     *
     * @param   string $videoId             Video ID.
     *
     * @return  array                       Video data.
     * @throws  InvalidArgumentException    Video ID is incorrect.
     * @throws  RuntimeException            Error on data reading process.
     */
    public function read(string $videoId): array
    {
        if (strlen($videoId) === 0) {
            throw new InvalidArgumentException('video id is empty');
        }

        $apiUri         = self::API_URI;
        $queryString    = http_build_query([
            'id'    => $videoId,
            'key'   => $this->googleApiKey,
            'part'  => implode(',', [
                'contentDetails',
                'statistics',
            ]),
        ]);
        $requestFullUri = "$apiUri/videos?$queryString";

        try {
            $response = $this->httpClient->request('GET', $requestFullUri);
        } catch (TransportExceptionInterface $exception) {
            throw new RuntimeException("request failed: {$exception->getMessage()}");
        }

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException("request failed with status {$response->getStatusCode()}");
        }

        return $response->toArray();
    }
}
