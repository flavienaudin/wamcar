<?php

namespace AppBundle\Security\SecurityTrait;

use AppBundle\Utils\TokenGenerator;

trait ApiCredentialTrait
{
    /** @var string */
    private $apiClientId;
    /** @var string */
    private $apiSecret;

    /**
     *
     */
    public function generateApiCredentials(): void
    {
        $this->apiClientId = TokenGenerator::generateToken();
        $this->apiSecret = TokenGenerator::generateToken();
    }

    /**
     * @return string
     */
    public function getApiClientId(): ?string
    {
        return $this->apiClientId;
    }

    /**
     * @return string
     */
    public function getApiSecret(): ?string
    {
        return $this->apiSecret;
    }
}
