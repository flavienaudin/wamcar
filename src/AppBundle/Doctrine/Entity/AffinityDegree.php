<?php

namespace AppBundle\Doctrine\Entity;


use Wamcar\User\BaseUser;

class AffinityDegree
{

    /** @var BaseUser */
    private $mainUser;
    /** @var BaseUser */
    private $withUser;
    /** @var float $affinityValue */
    private $affinityValue;

    /**
     * AffinityDegree constructor.
     * @param BaseUser $mainUser
     * @param BaseUser $withUser
     * @param float $affinityValue
     */
    public function __construct(BaseUser $mainUser, BaseUser $withUser, float $affinityValue)
    {
        $this->mainUser = $mainUser;
        $this->withUser = $withUser;
        $this->affinityValue = $affinityValue;
    }

    /**
     * @return BaseUser
     */
    public function getMainUser(): BaseUser
    {
        return $this->mainUser;
    }

    /**
     * @return BaseUser
     */
    public function getWithUser(): BaseUser
    {
        return $this->withUser;
    }

    /**
     * @return float
     */
    public function getAffinityValue(): float
    {
        return $this->affinityValue;
    }
}