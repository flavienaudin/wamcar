<?php


namespace AppBundle\MailWorkflow;


use AppBundle\Controller\Front\PersonalContext\RegistrationController;
use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\User\Event\UserCreated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;
use Symfony\Component\Routing\RouterInterface;

class NotifyUserOfRegistrationTokenGenerated extends AbstractEmailEventHandler implements UserEventHandler
{
    /**
     * @param UserEvent $event
     */
    public function notify(UserEvent $event)
    {
        $this->checkEventClass($event, UserCreated::class);

        /** @var ApplicationUser $user */
        $user = $event->getUser();
        $vehicleReplace = $event->isVehicleReplace();

        $this->send(
            $this->translator->trans('notifyUserOfRegistrationTokenGenerated.object', [], 'email'),
            'Mail/notifyUserOfRegistrationTokenGenerated.html.twig',
            [

                'username' => $user->getFirstName(),
                'activationUrl' => $this->router->generate('security_confirm_registration', ['token' => $user->getRegistrationToken()], RouterInterface::ABSOLUTE_URL),
                //'activationUrl' => $this->router->generate('security_confirm_registration', ['token' => $user->getRegistrationToken(), RegistrationController::VEHICLE_REPLACE_PARAM => $vehicleReplace], RouterInterface::ABSOLUTE_URL),
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );
    }
}
