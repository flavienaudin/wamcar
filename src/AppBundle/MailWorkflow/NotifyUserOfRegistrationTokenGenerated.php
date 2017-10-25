<?php


namespace AppBundle\MailWorkflow;


use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\MailWorkflow\Model\EmailContact;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
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

        $this->send(
            $this->translator->trans('notifyUserOfRegistrationTokenGenerated.title', [], 'email'),
            'Mail/notifyUserOfRegistrationTokenGenerated.html.twig',
            [
                'activationUrl' => $this->router->generate('security_confirm_registration', ['token' => $user->getRegistrationToken()], RouterInterface::ABSOLUTE_URL),
                'siteUrl' => $this->router->generate('front_default')
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );
    }
}
