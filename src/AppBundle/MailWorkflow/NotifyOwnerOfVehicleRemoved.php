<?php


namespace AppBundle\MailWorkflow;


use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\Vehicle\Event\VehicleEvent;
use Wamcar\Vehicle\Event\VehicleEventHandler;
use Wamcar\Vehicle\Event\VehicleRemoved;

class NotifyOwnerOfVehicleRemoved extends AbstractEmailEventHandler implements VehicleEventHandler
{
    /**
     * @param VehicleEvent $event
     */
    public function notify(VehicleEvent $event)
    {
        $this->checkEventClass($event, VehicleRemoved::class);

        /** @var ApplicationUser $user */
        $vehicle = $event->getVehicle();

        $this->send(
            $this->translator->trans('notifyOwnerOfVehicleRemoved.title', [], 'email'),
            'Mail/notifyOwnerOfVehicleRemoved.html.twig',
            [
                'name' => $vehicle->getName(),
                'annee' => $vehicle->getYears(),
                'siteUrl' => $this->router->generate('front_default', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList([$this->createUserEmailContact($vehicle->getSeller())])
        );
    }
}
