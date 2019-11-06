<?php


namespace Wamcar\User;


class ProUserProService
{

    /** @var int */
    private $id;
    /** @var ProUser */
    private $proUser;
    /** @var ProService */
    private $proService;
    /** @var bool */
    private $isSpeciality;

    /**
     * ProUserProService constructor.
     */
    public function __construct()
    {
        $this->isSpeciality = false;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ProUser
     */
    public function getProUser()
    {
        return $this->proUser;
    }

    /**
     * @param null|ProUser $proUser
     */
    public function setProUser(?ProUser $proUser)
    {
        $this->proUser = $proUser;
    }

    /**
     * @return ProService
     */
    public function getProService()
    {
        return $this->proService;
    }

    /**
     * @param ProService $proService
     */
    public function setProService(ProService $proService)
    {
        $this->proService = $proService;
    }

    /**
     * @return bool
     */
    public function isSpeciality(): bool
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