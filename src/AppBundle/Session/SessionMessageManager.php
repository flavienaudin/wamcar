<?php


namespace AppBundle\Session;


use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Session\Model\SessionMessage;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class SessionMessageManager
{
    const DRAFT_KEY = 'draft';

    /** @var SessionInterface */
    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function set(string $route, array $routeParams, MessageDTO $messageDTO): void
    {
        $sessionMessage = new SessionMessage($route, $routeParams, $messageDTO);
        $this->session->set(self::DRAFT_KEY, $sessionMessage);
    }
}
