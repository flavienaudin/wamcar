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

    /**
     * @return null|string
     */
    public function getRoute(): ?string
    {
        return $this->session->has(self::DRAFT_KEY) ? $this->session->get(self::DRAFT_KEY)->route : null;
    }

    /**
     * @return null|array
     */
    public function getRouteParams(): ?array
    {
        return $this->session->has(self::DRAFT_KEY) ? $this->session->get(self::DRAFT_KEY)->routeParams : null;
    }

    /**
     * @return null|MessageDTO
     */
    public function getMessageDTO(): ?MessageDTO
    {
        return $this->session->has(self::DRAFT_KEY) ? $this->session->get(self::DRAFT_KEY)->messageDTO : null;
    }
}
