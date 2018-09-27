<?php

namespace AppBundle\Doctrine\Entity;


use Wamcar\User\PersonalUser;
use Wamcar\User\ProUser;

class AffinityDegree
{

    /** @var ProUser $proUser */
    private $proUser;
    /** @var PersonalUser $personalUser */
    private $personalUser;
    /** @var float $proPersonalScore */
    private $proPersonalScore;
    /** @var float $personalProScore */
    private $personalProScore;

    /**
     * AffinityDegree constructor.
     * @param ProUser $proUser
     * @param PersonalUser $personalUser
     * @param float|null $proPersonalScore
     * @param float|null $personalProScore
     */
    public function __construct(ProUser $proUser, PersonalUser $personalUser, float $proPersonalScore = 0, float $personalProScore = 0)
    {
        $this->proUser = $proUser;
        $this->personalUser = $personalUser;
        $this->proPersonalScore = $proPersonalScore;
        $this->personalProScore = $personalProScore;
    }


    /**
     * @return ProUser
     */
    public function getProUser(): ProUser
    {
        return $this->proUser;
    }

    /**
     * @return PersonalUser
     */
    public function getPersonalUser(): PersonalUser
    {
        return $this->personalUser;
    }
}