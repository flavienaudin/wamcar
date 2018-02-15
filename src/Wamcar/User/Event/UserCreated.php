<?php


namespace Wamcar\User\Event;


use Wamcar\User\BaseUser;

class UserCreated extends AbstractUserEvent implements UserEvent
{
    /** @var bool */
    private $vehicleReplace;

    public function __construct(BaseUser $user, ?bool $vehicleReplace = false)
    {
        parent::__construct($user);
        $this->vehicleReplace = $vehicleReplace;
    }

    /**
     * @return bool
     */
    public function isVehicleReplace(): bool
    {
        return $this->vehicleReplace;
    }
}
