<?php

namespace AutoData;

use AutoData\Converter\ArrayToXMLConverter;
use AutoData\Exception\ExceptionFromResponseCode;
use AutoData\Exception\WebserviceCallException;
use AutoData\Request\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ServerException;
use Symfony\Component\Serializer\{
    Encoder\XmlEncoder, Serializer
};

class ApiConnector
{
    /** @var array */
    private $header;
    /** @var Client */
    private $client;

    /**
     * ApiConnector constructor.
     * @param string $host
     * @param string $version
     * @param string $environment
     * @param string $username
     * @param string $password
     * @param string $language
     */
    public function __construct(
        string $host,
        string $version,
        string $environment,
        string $username,
        string $password,
        string $language = 'fr')
    {
        $this->client = new Client([
            'base_uri' => $host,
        ]);

        $this->header = [
            'Version' => $version,
            'Env' => $environment,
            'Ip' => $_SERVER['SERVER_ADDR'],
            'CarType' => null,
            'Lang' => $language,
            'Identification' => [
                'Login' => $username,
                'Pwd' => $password,
            ],
        ];
    }

    /**
     * @param Request $request
     * @return array
     * @throws Exception\AutodataException
     */
    public function executeRequest(Request $request): array
    {
        $payload = [
            'Header' => $this->header,
            'Body' => [
                'Request' => $request->getName(),
                'Params' => $request->getParams(),
            ]
        ];

        $xmlPayload = ArrayToXMLConverter::convert($payload);

        try {
            $response = $this->client->request('POST', '/connect.php', ['body' => $xmlPayload]);
        } catch (ServerException $serverException) {
            throw WebserviceCallException::buildFromServerException($serverException);
        }

        if ($response->getStatusCode() >= 300) {
            throw new WebserviceCallException($response->getBody(), $response->getStatusCode());
        }

        $responseData = ArrayToXMLConverter::revert($response->getBody());
        if ($exception = ExceptionFromResponseCode::get($responseData['Error'], $responseData['ResultText'])) {
            throw $exception;
        }

        return $responseData[$request->getName()];
    }
}
