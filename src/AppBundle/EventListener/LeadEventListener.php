<?php

namespace AppBundle\EventListener;


use AppBundle\Services\User\LeadManagementService;
use Wamcar\Conversation\Event\MessageCreated;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\UserLikeVehicleEvent;
use Wamcar\User\ProUser;

class LeadEventListener
{

    /** @var LeadManagementService $leadManagementService */
    private $leadManagementService;

    /**
     * LeadEventListener constructor.
     * @param LeadManagementService $leadManagementService
     */
    public function __construct(LeadManagementService $leadManagementService)
    {
        $this->leadManagementService = $leadManagementService;
    }

    /**
     * @param MessageCreated $event
     */
    public function messageCreated(MessageCreated $event)
    {
        $message = $event->getMessage();
        $messageSender = $message->getUser();
        if ($messageSender instanceof BaseUser) {
            $recipient = $event->getInterlocutor();
            if ($recipient instanceof ProUser) {
                // Message from LeadUser(PersonalUser|ProUser) to ProUser
                $this->leadManagementService->increaseNbLeadMessage($recipient, $messageSender);
            }
            if ($messageSender instanceof ProUser) {
                // Message Sender is also a ProUser
                $this->leadManagementService->increaseNbProMessage($messageSender, $recipient);
            }
        }
    }

    /**
     * @param UserLikeVehicleEvent $event
     */
    public function userLikeVehicle(UserLikeVehicleEvent $event)
    {
        $likeVehicle = $event->getLikeVehicle();
        $liker = $likeVehicle->getUser();
        $seller = $likeVehicle->getVehicle()->getSeller();

        if ($liker instanceof ProUser) {
            $this->leadManagementService->updateNbProLikes($liker, $seller, $likeVehicle->getValue() == 1);
        }
        if ($seller instanceof ProUser) {
            $this->leadManagementService->updateNbLeadLikes($seller, $liker, $likeVehicle->getValue() == 1);
        }
    }

}