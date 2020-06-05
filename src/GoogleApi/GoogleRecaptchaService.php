<?php


namespace GoogleApi;


use AppBundle\Services\App\CaptchaVerificator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class GoogleRecaptchaService implements CaptchaVerificator
{
    /** @var string  */
    private $clientSidePostParameterName = 'g-recaptcha-response';
    /** @var Client */
    private $client;
    /** @var string */
    private $verifyUrl;
    /** @var string */
    private $secret;

    /**
     * GoogleRecaptchaService constructor.
     * @param string $verifyUrl
     * @param string $secret
     */
    public function __construct(string $verifyUrl, string $secret)
    {
        $this->verifyUrl = $verifyUrl;
        $this->secret = $secret;
        $this->client = new Client();
    }

    /**
     * @return string
     */
    public function getClientSidePostParameters()
    {
        return $this->clientSidePostParameterName;
    }

    public function verify(array $data): array
    {
        try {
            $validationResponse = $this->client->request('POST', $this->verifyUrl,[
                'form_params' => [
                    'secret' => $this->secret,
                    'response' => $data['token']
                ]
            ]);
            $responseData = json_decode($validationResponse->getBody(), true);
            dump($responseData);
            return [
                'success' => $responseData['success'],
                'error' => $responseData['error-codes'] ?? []
            ];
        }catch (GuzzleException $exception){
            return [
                'succes' => false,
                'error' => $exception->getMessage()
            ];
        }
    }

}