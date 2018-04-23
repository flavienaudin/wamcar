<?php

namespace GoogleApi;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;


class GoogleMapsApiConnector
{
    /** @var LoggerInterface */
    private $logger;
    /** @var Client */
    private $client;
    /** @var string */
    private $apiKey;
    /** @var string */
    private $placeDetailsPath;
    /** @var string */
    private $outputFormat;
    /** @var string */
    private $language;


    /**
     * ApiConnector constructor.
     * @param LoggerInterface $logger
     * @param string $host
     * @param string $key
     * @param string $placeDetailsPath
     * @param string $output
     * @param string $language
     */
    public function __construct(
        LoggerInterface $logger,
        string $host,
        string $key,
        string $placeDetailsPath,
        string $output = 'json',
        string $language = 'fr')
    {
        $this->logger = $logger;
        $this->client = new Client([
            'base_uri' => $host,
        ]);
        $this->apiKey = $key;
        $this->placeDetailsPath = $placeDetailsPath;

        $this->outputFormat = $output;
        $this->language = $language;
    }

    public function getPlaceDetails(string $placeId): ?array
    {
        try {
            $jsonReponse = $this->client->request('GET', $this->placeDetailsPath . $this->outputFormat, [
                'query' => [
                    'key' => $this->apiKey,
                    'placeid' => $placeId,
                    'language' => $this->language
                ]
            ]);
            $decodedResponse = json_decode($jsonReponse->getBody(), true);
            if(isset($decodedResponse['result'])){
                return $decodedResponse['result'];
            }
        } catch (GuzzleException $serverException) {
            $this->logger->error($serverException->getMessage());
        }
        return null;
    }
}