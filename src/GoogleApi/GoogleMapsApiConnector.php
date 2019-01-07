<?php

namespace GoogleApi;

use GoogleApi\Event\PlaceDetailError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use SimpleBus\Message\Bus\MessageBus;


class GoogleMapsApiConnector
{
    /** @var MessageBus */
    private $eventBus;
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
     * @param MessageBus $eventBus
     * @param LoggerInterface $logger
     * @param string $host
     * @param string $key
     * @param string $placeDetailsPath
     * @param string $output
     * @param string $language
     */
    public function __construct(
        MessageBus $eventBus,
        LoggerInterface $logger,
        string $host,
        string $key,
        string $placeDetailsPath,
        string $output = 'json',
        string $language = 'fr')
    {
        $this->eventBus = $eventBus;
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

            if (isset($decodedResponse['error_message'])) {
                $this->eventBus->handle(new PlaceDetailError(
                    $decodedResponse['status'],
                    $decodedResponse['error_message'], [
                        'placeDetailsPath' => $this->placeDetailsPath,
                        'outputFormat' => $this->outputFormat,
                        'query' => [
                            'key' => $this->apiKey,
                            'placeid' => $placeId,
                            'language' => $this->language
                        ]
                    ]
                ));
            }
            if ($decodedResponse['status'] === 'OK' && isset($decodedResponse['result'])) {
                return $decodedResponse['result'];
            }
        } catch (GuzzleException $serverException) {
            $this->logger->error($serverException->getMessage());
        }
        return null;
    }
}