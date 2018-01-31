<?php


namespace AppBundle\MailWorkflow;


use AppBundle\MailWorkflow\Model\EmailRecipientList;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\AddingPicturesToVehicleNotification;
use Wamcar\User\Event\UserEvent;
use Wamcar\User\Event\UserEventHandler;

class NotifyUserOfAddingPicturesToVehicle extends AbstractEmailEventHandler implements UserEventHandler
{
    /**
     * @param UserEvent $event
     */
    public function notify(UserEvent $event)
    {
        $this->checkEventClass($event, AddingPicturesToVehicleNotification::class);

        /** @var BaseUser $user */
        $user = $event->getUser();

        $this->send(
            $this->translator->trans('notifyUserOfAddingPicturesToVehicle.title', [], 'email'),
            'Mail/notifyUserOfAddingPicturesToVehicle.html.twig',
            [
                'username' => $user->getName(),
                'siteUrl' => $this->router->generate('front_default', [], UrlGeneratorInterface::ABSOLUTE_URL)
            ],
            new EmailRecipientList([$this->createUserEmailContact($user)])
        );
    }
}
