<?php


namespace AppBundle\Session;


use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Session\Model\Message;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class ConversationSession
{
    /** @var SessionInterface */
    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public function saveMessageDTOInSession(Request $request, MessageDTO $messageDTO)
    {
        $sessionMessage = new Message($request->get('_route'), $request->get('_route_params'), $messageDTO);
        $this->session->set('draft', $sessionMessage);
    }
}
