<?php


namespace Wamcar\Garage;


use AppBundle\Services\User\CanBeGarageMember;
use Wamcar\Garage\Enum\GarageRole;
use Wamcar\User\ProUser;

class GarageProUser
{
    /** @var  Garage */
    protected $garage;
    /** @var  CanBeGarageMember */
    protected $proUser;
    /** @var GarageRole */
    private $role;
    /** @var ?\DateTime */
    private $requestedAt;

    /**
     * GarageProUser constructor.
     * @param Garage $garage
     * @param CanBeGarageMember $proUser
     * @param GarageRole $garageRole
     */
    public function __construct(Garage $garage, CanBeGarageMember $proUser, GarageRole $garageRole)
    {
        $this->garage = $garage;
        $this->proUser = $proUser;
        $this->role = $garageRole;
    }

    /**
     * @return Garage
     */
    public function getGarage(): Garage
    {
        return $this->garage;
    }

    /**
     * @return CanBeGarageMember
     */
    public function getProUser(): ProUser
    {
        return $this->proUser;
    }

    /**
     * @return GarageRole
     */
    public function getRole(): GarageRole
    {
        return $this->role;
    }

    /**
     * @param GarageRole $role
     */
    public function setRole(GarageRole $role): void
    {
        $this->role = $role;
    }

    /**
     * @return \DateTime|null
     */
    public function getRequestedAt()
    {
        return $this->requestedAt;
    }

    /**
     * @param \DateTime|null $requestedAt
     */
    public function setRequestedAt($requestedAt): void
    {
        $this->requestedAt = $requestedAt;
    }
}
