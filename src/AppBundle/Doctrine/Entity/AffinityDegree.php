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
    /** @var float $profileAffinityValue */
    private $profileAffinityValue;
    /** @var float $passionAffinityValue */
    private $passionAffinityValue;
    /** @var float $positioningAffinityValue */
    private $positioningAffinityValue;
    /** @var float $atomesCrochusAffinityValue */
    private $atomesCrochusAffinityValue;

    /**
     * AffinityDegree constructor.
     * @param BaseUser $mainUser
     * @param BaseUser $withUser
     * @param float $affinityValue
     * @param float $profileAffinityValue
     * @param float $passionAffinityValue
     * @param float $positioningAffinityValue
     * @param float $atomesCrochusAffinityValue
     */
    public function __construct(BaseUser $mainUser, BaseUser $withUser, float $affinityValue, float $profileAffinityValue, float $passionAffinityValue, float $positioningAffinityValue, float $atomesCrochusAffinityValue)
    {
        $this->mainUser = $mainUser;
        $this->withUser = $withUser;
        $this->affinityValue = $affinityValue;
        $this->profileAffinityValue = $profileAffinityValue;
        $this->passionAffinityValue = $passionAffinityValue;
        $this->positioningAffinityValue = $positioningAffinityValue;
        $this->atomesCrochusAffinityValue = $atomesCrochusAffinityValue;
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