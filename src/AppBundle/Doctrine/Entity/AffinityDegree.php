<?php

namespace AppBundle\Doctrine\Entity;


use Wamcar\User\BaseUser;

class AffinityDegree
{

    /** @var BaseUser */
    private $smallerIdUser;
    /** @var BaseUser */
    private $greaterIdUser;
    /** @var float $affinityValue */
    private $affinityValue;
    /** @var float $profileAffinityValue */
    private $profileAffinityValue;
    /** @var float $linkingAffinityValue */
    private $linkingAffinityValue;
    /** @var float $passionAffinityValue */
    private $passionAffinityValue;
    /** @var float $positioningAffinityValue */
    private $positioningAffinityValue;
    /** @var float $atomesCrochusAffinityValue */
    private $atomesCrochusAffinityValue;

    /**
     * AffinityDegree constructor.
     * @param BaseUser $userA
     * @param BaseUser $userB
     * @param float $affinityValue
     * @param float $profileAffinityValue
     * @param float $linkingAffinityValue
     * @param float $passionAffinityValue
     * @param float $positioningAffinityValue
     * @param float $atomesCrochusAffinityValue
     */
    public function __construct(BaseUser $userA, BaseUser $userB, float $affinityValue, float $profileAffinityValue, float $linkingAffinityValue, float $passionAffinityValue, float $positioningAffinityValue, float $atomesCrochusAffinityValue)
    {
        if ($userA->getId() < $userB->getId()) {
            $this->smallerIdUser = $userA;
            $this->greaterIdUser = $userB;
        } else {
            $this->smallerIdUser = $userB;
            $this->greaterIdUser = $userA;
        }

        $this->affinityValue = $affinityValue;
        $this->profileAffinityValue = $profileAffinityValue;
        $this->linkingAffinityValue = $linkingAffinityValue;
        $this->passionAffinityValue = $passionAffinityValue;
        $this->positioningAffinityValue = $positioningAffinityValue;
        $this->atomesCrochusAffinityValue = $atomesCrochusAffinityValue;
    }

    /**
     * @return BaseUser
     */
    public function getSmallerIdUser(): BaseUser
    {
        return $this->smallerIdUser;
    }

    /**
     * @return BaseUser
     */
    public function getGreaterIdUser(): BaseUser
    {
        return $this->greaterIdUser;
    }

    /**
     * @return float
     */
    public function getAffinityValue(): float
    {
        return $this->affinityValue;
    }

    public function getRadarChartData(): array
    {
        return [
            'labels' => ['Total', 'Profil', 'Mise en relation', 'Passions', 'Positionnement', 'Atomes Crochus'],
            'datasets' => [[
                'label' => 'Affinités (%)',
                'data' => [
                    intval($this->affinityValue),
                    intval($this->profileAffinityValue),
                    intval($this->linkingAffinityValue),
                    intval($this->passionAffinityValue),
                    intval($this->positioningAffinityValue),
                    intval($this->atomesCrochusAffinityValue)
                ]
            ]]
        ];
    }
}