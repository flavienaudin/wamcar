<?php


namespace Wamcar\User;


use Wamcar\Vehicle\Vehicle;

class PersonalUser extends BaseUser
{
    const TYPE = 'personal';

    /** @var Vehicle[]|array */
    protected $vehicles;

    /**
     * PersonalUser constructor.
     * @param string $email
     * @param null $firstVehicle
     */
    public function __construct($email, $firstVehicle = null)
    {
        parent::__construct($email);

        $this->vehicles = $firstVehicle ? [$firstVehicle] : [];

    }

    /**
     * @return array|Vehicle[]
     */
    public function getVehicles()
    {
        return $this->vehicles;
    }
}
