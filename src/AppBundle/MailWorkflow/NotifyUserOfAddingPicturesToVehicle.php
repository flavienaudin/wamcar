<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\Vehicle\Event\AddingPicturesToVehicleNotification;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;
use Wamcar\Vehicle\PersonalVehicle;

class NotifyUserOfAddingPicturesToVehicle extends AbstractEmailEventHandler implements VehicleEventHandler
{
    /**
     * @param VehicleEvent $event
     */
    public function notify(VehicleEvent $event)
    {
        $this->checkEventClass($event, AddingPicturesToVehicleNotification::class);

        /** @var PersonalVehicle $vehicle */
        $vehicle = $event->getVehicle();
        $this->checkEventClass($vehicle, PersonalVehicle::class);
        $vehicleSeller = $vehicle->getSeller();
        $trackingKeywords = ($vehicleSeller ->isPro() ? 'advisor' : 'customer') . $vehicleSeller ->getId();

        $commonUTM = [
            'utm_source' => 'notifications',
            'utm_medium' => 'email',
            'utm_campaign' => 'classifiedads_addingphoto',
            'utm_term' => $trackingKeywords
        ];
        $this->send(
            $this->translator->trans('notifyUserOfAddingPicturesToVehicle.object', [], 'email'),
            'Mail/notifyUserOfAddingPicturesToVehicle.html.twig',
            [
                'common_utm' => $commonUTM ,
                'username' => $vehicle->getSellerName(true),
                'url_vehicle_page' => $this->router->generate('front_vehicle_personal_detail', array_merge(
                    $commonUTM, [
                        'utm_content' => 'bouton_1',
                        'slug' => $vehicle->getSlug()
                ]), UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList([$this->createUserEmailContact($vehicle->getOwner())])
        );
    }
}
