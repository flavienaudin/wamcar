<?php


namespace AppBundle\Session;


use AppBundle\Doctrine\Repository\DoctrinePersonalVehicleRepository;
use AppBundle\Doctrine\Repository\DoctrineProVehicleRepository;
use AppBundle\Form\DTO\MessageDTO;
use AppBundle\Services\Vehicle\VehicleRepositoryResolver;
use AppBundle\Session\Model\SessionMessage;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Wamcar\Vehicle\BaseVehicle;

class SessionMessageManager
{
    const DRAFT_KEY = 'draft';

    /** @var SessionInterface */
    protected $session;
    /** @var VehicleRepositoryResolver */
    protected $vehicleRepositoryResolver;

    /**
     * SessionMessageManager constructor.
     * @param SessionInterface $session
     * @param VehicleRepositoryResolver $vehicleRepositoryResolver
     */
    public function __construct(
        SessionInterface $session,
        VehicleRepositoryResolver $vehicleRepositoryResolver
    )
    {
        $this->session = $session;
        $this->vehicleRepositoryResolver = $vehicleRepositoryResolver;
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
            if ($sessionMessage->getVehicleHeaderId()) {
                $messageDTO->vehicleHeader = $this->vehicleRepositoryResolver->getVehicleRepositoryByVehicleHeaderSessionMessage($sessionMessage)->find($sessionMessage->getVehicleHeaderId());
            }
            if ($sessionMessage->getVehicleId()) {
                $messageDTO->vehicle = $this->vehicleRepositoryResolver->getVehicleRepositoryByVehicleSessionMessage($sessionMessage)->find($sessionMessage->getVehicleId());
            }

            return $messageDTO;
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
