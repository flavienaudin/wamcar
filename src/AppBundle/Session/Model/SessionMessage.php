<?php


namespace AppBundle\Session\Model;


use AppBundle\Form\DTO\MessageDTO;
use Wamcar\User\BaseUser;
use Wamcar\Vehicle\BaseVehicle;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\ProVehicle;

class SessionMessage
{
    /** @var string */
    public $route;
    /** @var array */
    public $routeParams;
    /** @var  BaseUser */
    public $user;
    /** @var  BaseUser */
    public $interlocutor;
    /** @var  string */
    public $content;
    /** @var null|string */
    protected $proVehicleHeaderId;
    /** @var null|string */
    protected $personalVehicleHeaderId;
    /** @var null|string */
    protected $proVehicleId;
    /** @var null|string */
    protected $personalVehicleId;

    /**
     * @param string $route
     * @param array $routeParams
     * @param MessageDTO $messageDTO
     * @return SessionMessage
     */
    public static function buildFromMessageDTO(string $route, array $routeParams, MessageDTO $messageDTO): SessionMessage
    {
        $sessionMessage = new self();
        $sessionMessage->route = $route;
        $sessionMessage->routeParams = $routeParams;
        $sessionMessage->user = $messageDTO->user;
        $sessionMessage->interlocutor = $messageDTO->interlocutor;
        $sessionMessage->content = $messageDTO->content;

        $sessionMessage->assignVehicleHeader($sessionMessage, $messageDTO->vehicleHeader);
        $sessionMessage->assignVehicle($sessionMessage, $messageDTO->vehicle);

        return $sessionMessage;
    }

    /**
     * @param SessionMessage $sessionMessage
     * @param null|BaseVehicle $vehicle
     */
    protected function assignVehicleHeader(SessionMessage $sessionMessage, ?BaseVehicle $vehicle = null): void
    {
        if ($vehicle instanceof ProVehicle) {
            $sessionMessage->proVehicleHeaderId = $vehicle->getId();
        } elseif ($vehicle instanceof PersonalVehicle) {
            $sessionMessage->personalVehicleHeaderId = $vehicle->getId();
        }
    }

    /**
     * @param SessionMessage $sessionMessage
     * @param null|BaseVehicle $vehicle
     */
    protected function assignVehicle(SessionMessage $sessionMessage, ?BaseVehicle $vehicle = null): void
    {
        if ($vehicle instanceof ProVehicle) {
            $sessionMessage->proVehicleId = $vehicle->getId();
        } elseif ($vehicle instanceof PersonalVehicle) {
            $sessionMessage->personalVehicleId = $vehicle->getId();
        }
    }

    /**
     * @return null|string
     */
    public function getVehicleId(): ?string
    {
        return $this->proVehicleId ?: $this->personalVehicleId;
    }

    /**
     * @return null|string
     */
    public function getVehicleHeaderId(): ?string
    {
        return $this->proVehicleHeaderId ?: $this->personalVehicleHeaderId;
    }

    /**
     * @return bool
     */
    public function isProVehicle(): bool
    {
        return $this->proVehicleId ?? false;
    }

    /**
     * @return bool
     */
    public function isPersonalVehicle(): bool
    {
        return $this->personalVehicleId ?? false;
    }

    /**
     * @return bool
     */
    public function isProVehicleHeader(): bool
    {
        return $this->proVehicleHeaderId ?? false;
    }

    /**
     * @return bool
     */
    public function isPersonalVehicleHeader(): bool
    {
        return $this->personalVehicleHeaderId ?? false;
    }
}
