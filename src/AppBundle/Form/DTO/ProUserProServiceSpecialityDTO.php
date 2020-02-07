<?php


namespace AppBundle\Form\DTO;


class ProUserProServiceSpecialityDTO
{

    /** @var int */
    private $proUserProServiceId;
    /** @var string */
    private $proServiceName;
    /** @var bool */
    private $isSpeciality;

    /**
     * ProUserProSpecialityDTO constructor.
     * @param int $proUserProServiceId
     * @param string $proServiceName
     * @param bool $isSpeciality
     */
    public function __construct(int $proUserProServiceId, string $proServiceName, bool $isSpeciality)
    {
        $this->proUserProServiceId = $proUserProServiceId;
        $this->proServiceName = $proServiceName;
        $this->isSpeciality = $isSpeciality;
    }

    /**
     * @return int
     */
    public function getProUserProServiceId()
    {
        return $this->proUserProServiceId;
    }

    /**
     * @return string
     */
    public function getProServiceName(): string
    {
        return $this->proServiceName;
    }

    /**
     * @return bool
     */
    public function isSpeciality()
    {
        return $this->isSpeciality;
    }

    /**
     * @param bool $isSpeciality
     */
    public function setIsSpeciality(bool $isSpeciality)
    {
        $this->isSpeciality = $isSpeciality;
    }

}