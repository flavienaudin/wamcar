<?php

namespace Wamcar\User\Event;


use Wamcar\Garage\Garage;

class EmailsInvitationsEvent extends AbstractInvitationsEvent implements InvitationsEvent
{


    /** @var Garage $garage */
    private $garage;

    /**
     * EmailsInvitationsEvent constructor.
     * @param array $invitations List of e-mails to send an invitation to
     * @param Garage $garage The garage to join
     */
    public function __construct(array $invitations, Garage $garage)
    {
        parent::__construct($invitations);
        $this->garage = $garage;
    }

    /**
     * @return Garage
     */
    public function getGarage(): Garage
    {
        return $this->garage;
    }
}