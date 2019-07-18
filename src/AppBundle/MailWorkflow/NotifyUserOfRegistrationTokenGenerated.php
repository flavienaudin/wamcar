<?php


namespace AppBundle\MailWorkflow;


use AppBundle\Controller\Front\PersonalContext\RegistrationController;
use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\RouterInterface;
use Wamcar\User\Event\UserCreated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;

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

        if (!$user->hasConfirmedRegistration()) {
            $trackingKeywords = ($user->isPro() ? 'advisor' : 'customer') . $user->getId();
            $commonUTM = [
                'utm_source' => 'notifications',
                'utm_medium' => 'email',
                'utm_campaign' => 'confirm_email_customer',
                'utm_term' => $trackingKeywords
            ];

            $this->send(
                $this->translator->trans('notifyUserOfRegistrationTokenGenerated.object', [], 'email'),
                'Mail/notifyUserOfRegistrationTokenGenerated.html.twig',
                [
                    'common_utm' => $commonUTM,
                    'username' => $user->getFirstName(),
                    'emailAddress' => $user->getEmail(),
                    'activationUrl' => $this->router->generate('security_confirm_registration', array_merge(
                        $commonUTM, [
                            'utm_content' => 'link_confirm_email',
                            'token' => $user->getRegistrationToken(),
                            RegistrationController::VEHICLE_REPLACE_PARAM => $event->isVehicleReplace()]
                    ), RouterInterface::ABSOLUTE_URL),
                ],
                new EmailRecipientList([$this->createUserEmailContact($user)])
            );
        }
    }
}
