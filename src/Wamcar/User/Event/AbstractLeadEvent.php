<?php


namespace Wamcar\User\Event;


use Wamcar\User\BaseUser;
use Wamcar\User\ProUser;

class AbstractLeadEvent
{

    /** @var ProUser */
    private $leadOwner;
    /** @var BaseUser */
    private $leadUser;

    /**
     * AbstractLeadEvent constructor.
     * @param ProUser $leadOwner
     * @param BaseUser $leadUser
     */
    public function __construct(ProUser $leadOwner, BaseUser $leadUser)
    {
        $this->leadOwner = $leadOwner;
        $this->leadUser = $leadUser;
    }

    /**
     * @return ProUser
     */
    public function getLeadOwner(): ProUser
    {
        return $this->leadOwner;
    }

    /**
     * @return BaseUser
     */
    public function getLeadUser(): BaseUser
    {
        return $this->leadUser;
    }
}