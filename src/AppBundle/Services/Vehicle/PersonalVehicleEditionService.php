<?php
/**
 * Created by PhpStorm.
 * User: flavien
 * Date: 10/01/18
 * Time: 10:57
 */

namespace AppBundle\Services\Vehicle;


use AppBundle\Form\DTO\PersonalVehicleDTO;
use AppBundle\Form\DTO\UserRegistrationPersonalVehicleDTO;
use AppBundle\Form\EntityBuilder\PersonalVehicleBuilder;
use AppBundle\Security\UserRegistrationService;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\User\PersonalUser;
use Wamcar\Vehicle\Event\PersonalVehicleCreated;
use Wamcar\Vehicle\Event\PersonalVehicleRemoved;
use Wamcar\Vehicle\Event\PersonalVehicleUpdated;
use Wamcar\Vehicle\PersonalVehicle;
use Wamcar\Vehicle\PersonalVehicleRepository;

class PersonalVehicleEditionService
{

    /** @var PersonalVehicleRepository */
    private $vehicleRepository;
    /** @var PersonalVehicleBuilder */
    private $vehicleBuilder;
    /** @var UserRegistrationService */
    private $userRegistrationService;
    /** @var MessageBus */
    private $eventBus;

    /**
     * PersonalVehicleEditionService constructor.
     *
     * @param PersonalVehicleRepository $vehicleRepository
     * @param PersonalVehicleBuilder $personalVehicleBuilder
     * @@param UserRegistrationService $userRegistrationService
     * @param MessageBus $eventBus
     */
    public function __construct(
        PersonalVehicleRepository $vehicleRepository,
        PersonalVehicleBuilder $personalVehicleBuilder,
        UserRegistrationService $userRegistrationService,
        MessageBus $eventBus
    )
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleBuilder = $personalVehicleBuilder;
        $this->userRegistrationService = $userRegistrationService;
        $this->eventBus = $eventBus;
    }

    /**
     * @param PersonalVehicleDTO $personalVehicleDTO
     * @param PersonalUser|null $futurOwner
     * @return PersonalVehicle
     * @throws \Exception
     */
    public function createInformations(PersonalVehicleDTO $personalVehicleDTO, PersonalUser $futurOwner = null): PersonalVehicle
    {
        /** @var PersonalVehicle $personalVehicle */
        $personalVehicle = PersonalVehicleBuilder::buildFromDTO($personalVehicleDTO);

        if ($futurOwner == null && $personalVehicleDTO instanceof UserRegistrationPersonalVehicleDTO) {
            $futurOwner = $this->userRegistrationService->registerUser($personalVehicleDTO->userRegistration);
        }

        if ($futurOwner instanceof PersonalUser) {
            $personalVehicle->setOwner($futurOwner);
        }

        $this->vehicleRepository->add($personalVehicle);
        $this->eventBus->handle(new PersonalVehicleCreated($personalVehicle));

        return $personalVehicle;
    }

    /**
     * @param PersonalVehicleDTO $personalVehicleDTO
     * @param PersonalVehicle $vehicle
     * @return PersonalVehicle
     */
    public function updateInformations(PersonalVehicleDTO $personalVehicleDTO, PersonalVehicle $vehicle): PersonalVehicle
    {
        /** @var PersonalVehicle $personalVehicle */
        $personalVehicle = PersonalVehicleBuilder::editVehicleFromDTO($personalVehicleDTO, $vehicle);

        $this->vehicleRepository->update($personalVehicle);
        $this->eventBus->handle(new PersonalVehicleUpdated($personalVehicle));
        return $vehicle;
    }

    /**
     * @param PersonalVehicle $personalVehicle
     * @return PersonalVehicle
     */
    public function deleteVehicle(PersonalVehicle $personalVehicle): PersonalVehicle
    {
        $this->vehicleRepository->remove($personalVehicle);
        $this->eventBus->handle(new PersonalVehicleRemoved($personalVehicle));
        return $personalVehicle;
    }

    /**
     * @param $user
     * @param PersonalVehicle $vehicle
     * @return bool
     */
    public function canEdit($user = null, PersonalVehicle $vehicle): bool
    {
        return $vehicle != null && $vehicle->canEditMe($user);
    }

    /**
     * @param $user
     * @param PersonalVehicle $vehicle
     * @return bool
     */
    public function canSeeVehicle($user = null, PersonalVehicle $vehicle): bool
    {
        return $vehicle->getOwner() != null && $vehicle->canEditMe($user);
    }

    /**
     * Retrieve the PersonalUser registrered since 24H with a vehicle with 0 or 1 picture
     * @return PersonalVehicle[]
     */
    public function findPersonalToRemind()
    {
        return $this->vehicleRepository->retrieveVehiclesWithLessThan2PicturesSince24h();
    }
}
