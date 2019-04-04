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

    /**
     * Get or create a $proUser's Lead of $userLead
     * @param ProUser $proUser
     * @param BaseUser $user
     * @param bool $bddSave if true the new Lead is persisted in the db
     * @return Lead|null null if trying to get himself lead
     */
    private function getLead(ProUser $proUser, BaseUser $user, bool $bddSave = true): ?Lead
    {
        if ($proUser->is($user)) {
            return null;
        }
        $lead = $proUser->getLeadOfUser($user);
        if ($lead == null) {
            $lead = $this->createLead($proUser, $user, $bddSave);
        }
        return $lead;
    }

    /**
     * Create a new $proUser's Lead of $userLead
     * @param ProUser $proUser
     * @param BaseUser $userLead
     * @param bool $bddSave if true the new Lead is persisted in the db
     * @return null|Lead null if trying to create himself lead
     */
    public function createLead(ProUser $proUser, BaseUser $userLead, bool $bddSave = true): ?Lead
    {
        if ($proUser->is($userLead)) {
            return null;
        }
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
     * @return Lead|null if message to himself
     */
    public function increaseMessageNumberOfProUser(ProUser $proUser, BaseUser $messageSender): ?Lead
    {
        $lead = $this->getLead($proUser, $messageSender, false);
        if ($lead != null) {
            $lead->increaseNbMessages();
            return $this->leadRepository->update($lead);
        }
        return null;

    }

    /**
     * Create/Update the $liker's lead of the $proUser by incrementing the number of likes
     * @param ProUser $proUser
     * @param BaseUser $liker
     * @param bool $increase if true then nb of likes is increased, otherwise it is decreased
     * @return Lead|null if like to its own vehicle
     */
    public function updateLikeNumberOfProUser(ProUser $proUser, BaseUser $liker, bool $increase = true): ?Lead
    {
        $lead = $this->getLead($proUser, $liker, false);
        if ($lead != null) {
            $lead->increaseNbLikes($increase ? 1 : -1);
            return $this->leadRepository->update($lead);
        }
        return null;
    }

    /**
     * Create/Update the $messageSender's lead of the $proUser by incrementing the number of messages
     * @param ProUser $proUser
     * @param BaseUser $messageSender
     * @param bool $phonePro If true the phone number concerned is the ProUser.phonePro
     * @return Lead|null if $proUser's own phoneNumber
     */
    public function increasePhoneNumberOfProUser(ProUser $proUser, BaseUser $messageSender, bool $phonePro): ?Lead
    {
        $lead = $this->getLead($proUser, $messageSender, false);
        if ($lead != null) {
            if ($phonePro) {
                $lead->increaseNbPhoneProAction();
            } else {
                $lead->increaseNbPhoneAction();
            }
            return $this->leadRepository->update($lead);
        }
        return null;
    }

}