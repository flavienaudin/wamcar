<?php

namespace AppBundle\EventListener;


use AppBundle\Services\User\LeadManagementService;
use Wamcar\Conversation\Event\MessageCreated;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\UserLikeVehicleEvent;
use Wamcar\User\ProLikeVehicle;
use Wamcar\User\ProUser;
use Wamcar\Vehicle\ProVehicle;

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
                // Recipient is ProUser
                $this->leadManagementService->increaseMessageNumberOfProUser($recipient, $messageSender);
            }
            if ($messageSender instanceof ProUser) {
                // Message Sender is also a ProUser
                $this->leadManagementService->increaseMessageNumberOfProUser($messageSender, $recipient);
            }
        }
    }

    /**
     * @param UserLikeVehicleEvent $event
     */
    public function userLikeVehicle(UserLikeVehicleEvent $event)
    {
        $likeVehicle = $event->getLikeVehicle();
        if ($likeVehicle instanceof ProLikeVehicle) {
            /** @var ProVehicle $proVehicle */
            $proVehicle = $likeVehicle->getVehicle();
            $this->leadManagementService->increaseLikeNumberOfProUser($proVehicle->getSeller(), $likeVehicle->getUser());
        }

    }

}