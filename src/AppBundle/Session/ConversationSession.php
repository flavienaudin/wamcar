<?php


namespace AppBundle\Session;


use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Session\Model\Message;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ConversationSession
{
    /** @var SessionInterface */
    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function saveMessageDTOInSession(string $route, array $routeParams, MessageDTO $messageDTO): void
    {
        $sessionMessage = new Message($route, $routeParams, $messageDTO);
        $this->session->set('draft', $sessionMessage);
    }
}
