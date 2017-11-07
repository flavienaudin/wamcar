<?php

namespace AppBundle\Security\SecurityInterface;

interface ApiUserProvider
{
    /**
     * @param string $clientId
     * @return HasApiCredential
     */
    public function getByClientId(string $clientId): ?HasApiCredential;
}
