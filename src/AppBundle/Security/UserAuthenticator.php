<?php

namespace AppBundle\Security;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class UserAuthenticator
{
    /** @var RequestStack */
    private $requestStack;
    /** @var TokenStorageInterface */
    private $securityTokenStorage;
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * UserAuthenticator constructor.
     * @param RequestStack $requestStack
     * @param TokenStorageInterface $securityTokenStorage
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(RequestStack $requestStack, TokenStorageInterface $securityTokenStorage, EventDispatcherInterface $eventDispatcher)
    {
        $this->requestStack = $requestStack;
        $this->securityTokenStorage = $securityTokenStorage;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function authenticate(UserInterface $user, string $firewallName = 'front')
    {
        $token = new UsernamePasswordToken($user, null, $firewallName, $user->getRoles());
        $this->securityTokenStorage->setToken($token);

        $loginEvent = new InteractiveLoginEvent($this->requestStack->getCurrentRequest(), $token);
        $this->eventDispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
    }
}
