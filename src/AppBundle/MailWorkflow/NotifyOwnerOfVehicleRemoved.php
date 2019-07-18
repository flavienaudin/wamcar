<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\Event\PersonalVehicleRemoved;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;

class NotifyOwnerOfVehicleRemoved extends AbstractEmailEventHandler implements VehicleEventHandler
{
    /**
     * @param VehicleEvent $event
     */
    public function notify(VehicleEvent $event)
    {
        $this->checkEventClass($event, PersonalVehicleRemoved::class);

        /** @var BaseVehicle $user */
        $vehicle = $event->getVehicle();
        $vehicleOwner = $vehicle->getSeller();
        $trackingKeywords = ($vehicleOwner->isPro() ? 'advisor' : 'customer') . $vehicleOwner->getId();

        $commonUTM = [
            'utm_source' => 'notifications',
            'utm_medium' => 'email',
            'utm_campaign' => 'classifiedads_deleted',
            'utm_term' => $trackingKeywords
        ];
        $this->send(
            $this->translator->trans('notifyOwnerOfVehicleRemoved.object', [], 'email'),
            'Mail/notifyOwnerOfVehicleRemoved.html.twig',
            [
                'common_utm' => $commonUTM,
                'username' => $vehicle->getSellerName(true),
                'name' => $vehicle->getName(),
                'annee' => $vehicle->getYears()
            ],
            new EmailRecipientList([$this->createUserEmailContact($vehicle->getSeller())])
        );
    }
}
