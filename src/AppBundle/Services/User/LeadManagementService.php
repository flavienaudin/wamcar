<?php

namespace AppBundle\Services\User;


use Wamcar\User\BaseUser;
use Wamcar\User\Lead;
use Wamcar\User\LeadRepository;
use Wamcar\User\ProUser;

class LeadManagementService
{

    /** @var LeadRepository */
    private $leadRepository;

    /**
     * LeadManagementService constructor.
     * @param LeadRepository $leadRepository
     */
    public function __construct(LeadRepository $leadRepository)
    {
        $this->leadRepository = $leadRepository;
    }

    public function createLead(ProUser $proUser, BaseUser $userLead, bool $bddSave = true)
    {
        $lead = new Lead($proUser, $userLead);

        if ($bddSave) {
            $this->leadRepository->add($lead);
        }

        return $lead;
    }

    /**
     * Create/Update the $messageSender's lead of the $proUser by incrementing the number of messages
     * @param ProUser $proUser
     * @param BaseUser $messageSender
     * @return Lead
     */
    public function increaseMessageNumberOfProUser(ProUser $proUser, BaseUser $messageSender)
    {
        $lead = $proUser->getLeadOfUser($messageSender);
        if ($lead == null) {
            $lead = $this->createLead($proUser, $messageSender, false);
        }
        $lead->increaseNbMessages();
        return $this->leadRepository->update($lead);

    }

    /**
     * Create/Update the $liker's lead of the $proUser by incrementing the number of likes
     * @param ProUser $proUser
     * @param BaseUser $liker
     * @return Lead
     */
    public function increaseLikeNumberOfProUser(ProUser $proUser, BaseUser $liker)
    {
        $lead = $proUser->getLeadOfUser($liker);
        if ($lead == null) {
            $lead = $this->createLead($proUser, $liker, false);
        }
        $lead->increaseNbLikes();
        return $this->leadRepository->update($lead);
    }


}