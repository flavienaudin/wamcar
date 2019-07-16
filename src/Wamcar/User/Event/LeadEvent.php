<?php


namespace Wamcar\User\Event;


use Wamcar\User\BaseUser;
use Wamcar\User\ProUser;

interface LeadEvent
{
    /**
     * AbstractLeadEvent constructor.
     * @param ProUser $leadOwner
     * @param BaseUser $leadUser
     */
    public function __construct(ProUser $leadOwner, BaseUser $leadUser);

    /**
     * @return ProUser
     */
    public function getLeadOwner(): ProUser;

    /**
     * @return BaseUser
     */
    public function getLeadUser(): BaseUser;

}