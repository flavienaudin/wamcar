<?php

namespace AppBundle\Security\SecurityInterface;

interface HasApiCredential
{
    /**
     *
     */
    public function generateApiCredentials(): void;

    /**
     * @return string
     */
    public function getApiClientId(): ?string;

    /**
     * @return string
     */
    public function getApiSecret(): ?string;
}
