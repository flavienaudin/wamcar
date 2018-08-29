<?php
/**
 * Created by PhpStorm.
 * User: flavien
 * Date: 10/01/18
 * Time: 10:57
 */

namespace AppBundle\Services\Vehicle;


use AppBundle\Doctrine\Repository\DoctrineLikePersonalVehicleRepository;
use AppBundle\Form\DTO\PersonalVehicleDTO;
use AppBundle\Form\DTO\UserRegistrationPersonalVehicleDTO;
use AppBundle\Form\EntityBuilder\PersonalVehicleBuilder;
use AppBundle\Security\UserRegistrationService;
use Novaway\ElasticsearchClient\Query\Result;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\UserLikeVehicleEvent;
use Wamcar\User\PersonalLikeVehicle;
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
    /** @var DoctrineLikePersonalVehicleRepository */
    private $likePersonalVehicleRepository;
    /** @var MessageBus */
    private $eventBus;

    /**
     * PersonalVehicleEditionService constructor.
     *ProVehicleEditionService
     * @param PersonalVehicleRepository $vehicleRepository
     * @param PersonalVehicleBuilder $personalVehicleBuilder
     * @@param UserRegistrationService $userRegistrationService
     * @param DoctrineLikePersonalVehicleRepository $likePersonalVehicleRepository
     * @param MessageBus $eventBus
     */
    public function __construct(
        PersonalVehicleRepository $vehicleRepository,
        PersonalVehicleBuilder $personalVehicleBuilder,
        UserRegistrationService $userRegistrationService,
        DoctrineLikePersonalVehicleRepository $likePersonalVehicleRepository,
        MessageBus $eventBus
    )
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->vehicleBuilder = $personalVehicleBuilder;
        $this->userRegistrationService = $userRegistrationService;
        $this->likePersonalVehicleRepository = $likePersonalVehicleRepository;
        $this->eventBus = $eventBus;
    }

    /**
     * Retrieve PersonalVehicles from the search result
     * @param Result $searchResult
     * @return array
     */
    public function getVehiclesBySearchResult(Result $searchResult): array
    {
        $result = array();
        $result['totalHits'] = $searchResult->totalHits();
        $result['hits'] = array();
        $ids = array();
        foreach ($searchResult->hits() as $vehicle) {
            $ids[] = $vehicle['id'];
        }
        if(count($ids)> 0 ) {
            $result['hits'] = $this->vehicleRepository->findByIds($ids);
        }
        return $result;
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
            $futurOwner = $this->userRegistrationService->registerUser($personalVehicleDTO->userRegistration, (bool)$personalVehicleDTO->vehicleReplace);
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

    /**
     * Create a new PersonalLikeVehicle with value to 1 or update the existing PersonalLikeVehicle with toggled value     *
     * @param BaseUser $user The user who likes
     * @param PersonalVehicle $vehicle The liked vehicle
     */
    public function userLikesVehicle(BaseUser $user, PersonalVehicle $vehicle)
    {
        $like = $this->likePersonalVehicleRepository->findOneByUserAndVehicle($user, $vehicle);
        if ($like === null) {
            $like = new PersonalLikeVehicle($user, $vehicle, 1);
            $this->likePersonalVehicleRepository->add($like);
        } else {
            if ($like->getValue() === 1) {
                $like->setValue(0);
            } elseif ($like->getValue() === 0) {
                $like->setValue(1);
            }
            $this->likePersonalVehicleRepository->update($like);
        }
        $this->eventBus->handle(new PersonalVehicleUpdated($vehicle));
        $this->eventBus->handle(new UserLikeVehicleEvent($like));
    }
}
