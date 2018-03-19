<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\User\BaseUser;
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

        /** @var BaseUser $user */
        $user = $event->getUser();

        // TODO : Adapater l'email ? identifiant = email mais mot de passe non connu de l'utilisateur
        $this->send(
            $this->translator->trans('notifyProUserOfRegistration.object', [], 'email'),
            'Mail/notifyProUserOfRegistration.html.twig',
            [
                'username' => $user->getFirstName(),
                'user_mail' => $user->getEmail(),
                'url_help_page' => $this->router->generate("front_default", [], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );
    }
}
