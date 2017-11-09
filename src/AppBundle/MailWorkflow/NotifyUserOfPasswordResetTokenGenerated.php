<?php


namespace AppBundle\MailWorkflow;


use AppBundle\Doctrine\Entity\ApplicationUser;
use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\User\Event\UserCreated;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;
use Symfony\Component\Routing\RouterInterface;
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
            $this->translator->trans('notifyUserOfPasswordResetTokenGenerated.title', [], 'email'),
            'Mail/notifyUserOfPasswordResetTokenGenerated.html.twig',
            [
                'resetUrl' => $this->router->generate('security_password_reset', ['token' => $user->getPasswordResetToken()], RouterInterface::ABSOLUTE_URL),
                'siteUrl' => $this->router->generate('front_default', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );
    }
}
