<?php


namespace AppBundle\MailWorkflow;


use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\RouterInterface;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;
use Wamcar\User\Event\UserPasswordResetTokenGenerated;

class NotifyUserOfPasswordResetTokenGenerated extends AbstractEmailEventHandler implements UserEventHandler
{
    /**
     * @param UserEvent $event
     */
    public function notify(UserEvent $event)
    {
        $this->checkEventClass($event, UserPasswordResetTokenGenerated::class);

        /** @var ApplicationUser $user */
        $user = $event->getUser();

        $this->send(
            $this->translator->trans('notifyUserOfPasswordResetTokenGenerated.object', [], 'email'),
            'Mail/notifyUserOfPasswordResetTokenGenerated.html.twig',
            [
                'username' => $user->getFirstName(),
                'resetUrl' => $this->router->generate('security_password_reset', ['token' => $user->getPasswordResetToken()], RouterInterface::ABSOLUTE_URL),
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );
    }
}
