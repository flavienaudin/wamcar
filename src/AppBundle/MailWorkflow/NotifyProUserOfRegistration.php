<?php


namespace AppBundle\MailWorkflow;


use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\User\Event\ProUserCreated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;

class NotifyProUserOfRegistration extends AbstractEmailEventHandler implements UserEventHandler
{
    /**
     * @param UserEvent $event
     */
    public function notify(UserEvent $event)
    {
        $this->checkEventClass($event, ProUserCreated::class);

        /** @var ApplicationUser $user */
        $user = $event->getUser();

        $this->send(
            $this->translator->trans('notifyProUserOfRegistration.title', [], 'email'),
            'Mail/notifyProUserOfRegistration.html.twig',
            [
                'siteUrl' => $this->router->generate('front_default', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );
    }
}
