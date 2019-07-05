<?php


namespace AppBundle\EventListener;


use AppBundle\Doctrine\Entity\ApplicationUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Wamcar\User\UserRepository;

class AuthenticationEventListener implements EventSubscriberInterface
{
    /** @var UserRepository */
    private $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onAuthenticationSuccess',
        ];
    }

    public function onAuthenticationSuccess(InteractiveLoginEvent $event)
    {
        /** @var ApplicationUser $currentUser */
        $currentUser = $event->getAuthenticationToken()->getUser();
        $currentUser->setLastLoginAt(new \DateTime());
        $this->userRepository->update($currentUser);
    }
}