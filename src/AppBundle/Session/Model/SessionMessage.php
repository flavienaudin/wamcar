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
    public $proVehicleHeaderId;
    /** @var null|string */
    public $personalVehicleHeaderId;
    /** @var null|string */
    public $proVehicleId;
    /** @var null|string */
    public $personalVehicleId;

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

        if ($messageDTO->vehicleHeader instanceof ProVehicle) {
            $sessionMessage->proVehicleHeaderId = $messageDTO->vehicleHeader->getId();
        } elseif ($messageDTO->vehicleHeader instanceof PersonalVehicle) {
            $sessionMessage->personalVehicleHeaderId = $messageDTO->vehicleHeader->getId();
        }

        if ($messageDTO->vehicle instanceof ProVehicle) {
            $sessionMessage->proVehicleId = $messageDTO->vehicle->getId();
        } elseif ($messageDTO->vehicle instanceof PersonalVehicle) {
            $sessionMessage->personalVehicleId = $messageDTO->vehicle->getId();
        }

        return $sessionMessage;
    }
}
