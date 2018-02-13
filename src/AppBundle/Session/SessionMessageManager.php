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

    /**
     * @param string $route
     * @param array $routeParams
     * @param MessageDTO $messageDTO
     */
    public function set(string $route, array $routeParams, MessageDTO $messageDTO): void
    {
        $sessionMessage = new SessionMessage($route, $routeParams, $messageDTO);
        $this->session->set(self::DRAFT_KEY, $sessionMessage);
    }


    public function get(): ?SessionMessage
    {
        return $this->session->has(self::DRAFT_KEY) ? $this->session->get(self::DRAFT_KEY) : null;
    }

    /**
     * @return null|string
     */
    public function getRoute(): ?string
    {
        return $this->get() ? $this->get()->route : null;
    }

    /**
     * @return null|array
     */
    public function getRouteParams(): ?array
    {
        return $this->get() ? $this->get()->routeParams : null;
    }

    /**
     * @return null|MessageDTO
     */
    public function getMessageDTO(): ?MessageDTO
    {
        return $this->get() ? $this->get()->messageDTO : null;
    }
}
