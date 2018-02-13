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

    /**
     * @return SessionMessage|null
     */
    public function get(): ?SessionMessage
    {
        return $this->session->has(self::DRAFT_KEY) ? $this->session->get(self::DRAFT_KEY) : null;
    }

    /**
     * @return null|string
     */
    public function getRoute(): ?string
    {
        $sessionMessage = $this->get();
        return $sessionMessage ? $sessionMessage->route : null;
    }

    /**
     * @return null|array
     */
    public function getRouteParams(): ?array
    {
        $sessionMessage = $this->get();
        return $sessionMessage ? $sessionMessage->routeParams : null;
    }

    /**
     * @return null|MessageDTO
     */
    public function getMessageDTO(): ?MessageDTO
    {
        $sessionMessage = $this->get();
        return $sessionMessage ? $sessionMessage->messageDTO : null;
    }

    /**
     * Remove Draft message in session
     */
    public function remove(): void
    {
        $this->session->remove(self::DRAFT_KEY);
    }
}
