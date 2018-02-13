<?php


namespace AppBundle\Session;


use AppBundle\Doctrine\Repository\DoctrinePersonalVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineProVehicleRepository;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Session\Model\SessionMessage;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Wamcar\Vehicle\BaseVehicle;

class SessionMessageManager
{
    const DRAFT_KEY = 'draft';

    /** @var SessionInterface */
    protected $session;
    /** @var DoctrineProVehicleRepository */
    protected $proVehicleRepository;
    /** @var DoctrinePersonalVehicleRepository */
    protected $personalVehicleRepository;

    public function __construct(
        SessionInterface $session,
        DoctrineProVehicleRepository $proVehicleRepository,
        DoctrinePersonalVehicleRepository $personalVehicleRepository
    )
    {
        $this->session = $session;
        $this->proVehicleRepository = $proVehicleRepository;
        $this->personalVehicleRepository = $personalVehicleRepository;
    }

    /**
     * @param string $route
     * @param array $routeParams
     * @param MessageDTO $messageDTO
     */
    public function set(string $route, array $routeParams, MessageDTO $messageDTO): void
    {
        $sessionMessage = SessionMessage::buildFromMessageDTO($route, $routeParams, $messageDTO);
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

        if ($sessionMessage) {
            $messageDTO = new MessageDTO(null, $sessionMessage->user, $sessionMessage->interlocutor);
            $messageDTO->content = $sessionMessage->content;
            $messageDTO->vehicleHeader = $this->getVehicleHeaderSession($sessionMessage);
            $messageDTO->vehicle = $this->getVehicleSession($sessionMessage);

            return $messageDTO;
        }

        return null;
    }

    /**
     * @param SessionMessage $sessionMessage
     * @return null|BaseVehicle
     */
    public function getVehicleHeaderSession(SessionMessage $sessionMessage): ?BaseVehicle
    {
        if ($sessionMessage->proVehicleHeaderId) {
            return $this->proVehicleRepository->find($sessionMessage->proVehicleHeaderId);
        }
        if ($sessionMessage->personalVehicleHeaderId) {
            return $this->personalVehicleRepository->find($sessionMessage->personalVehicleHeaderId);
        }

        return null;
    }

    /**
     * @param SessionMessage $sessionMessage
     * @return null|BaseVehicle
     */
    public function getVehicleSession(SessionMessage $sessionMessage): ?BaseVehicle
    {
        if ($sessionMessage->proVehicleId) {
            return $this->proVehicleRepository->find($sessionMessage->proVehicleId);
        }
        if ($sessionMessage->personalVehicleId) {
            return $this->personalVehicleRepository->find($sessionMessage->personalVehicleId);
        }

        return null;
    }

    /**
     * Remove Draft message in session
     */
    public function clear(): void
    {
        $this->session->remove(self::DRAFT_KEY);
    }
}
