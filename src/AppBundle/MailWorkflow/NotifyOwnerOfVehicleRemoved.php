<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Wamcar\Vehicle\Event\PersonalVehicleRemoved;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;
use Wamcar\Vehicle\PersonalVehicle;

class NotifyOwnerOfVehicleRemoved extends AbstractEmailEventHandler implements VehicleEventHandler
{
    /**
     * @param VehicleEvent $event
     */
    public function notify(VehicleEvent $event)
    {
        $this->checkEventClass($event, PersonalVehicleRemoved::class);
        /** @var PersonalVehicle $vehicle */
        $vehicle = $event->getVehicle();
        $this->checkEventClass($event, PersonalVehicle::class);

        $commonUTM = [
            'utm_source' => 'notifications',
            'utm_medium' => 'email',
            'utm_campaign' => 'classifiedads_deleted',
            'utm_term' => 'customer' . $vehicle->getOwner()->getId()
        ];
        $this->send(
            $this->translator->trans('notifyOwnerOfVehicleRemoved.object', [], 'email'),
            'Mail/notifyOwnerOfVehicleRemoved.html.twig',
            [
                'common_utm' => $commonUTM,
                'username' => $vehicle->getOwnerName(true),
                'name' => $vehicle->getName(),
                'annee' => $vehicle->getYears()
            ],
            new EmailRecipientList([$this->createUserEmailContact($vehicle->getOwner())])
        );
    }
}
