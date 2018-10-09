<?php

namespace AppBundle\Services\Garage;

use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Exception\Garage\AlreadyGarageMemberException;
use AppBundle\Exception\Garage\ExistingGarageException;
use AppBundle\Form\Builder\Garage\GarageFromDTOBuilder;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use GoogleApi\GoogleMapsApiConnector;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Enum\GarageRole;
use Wamcar\Garage\Event\GarageUpdated;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Garage\GarageProUserRepository;
use Wamcar\Garage\GarageRepository;


class GarageEditionService
{
    /** @var GarageRepository */
    private $garageRepository;
    /** @var GarageProUserRepository */
    private $garageProUserRepository;
    /** @var GarageFromDTOBuilder */
    private $garageBuilder;
    /** @var ProVehicleEditionService */
    private $proVehicleEditionService;
    /** @var GoogleMapsApiConnector */
    private $googleMapsApi;
    /** @var MessageBus */
    protected $eventBus;

    /**
     * GarageEditionService constructor.
     * @param GarageRepository $garageRepository
     * @param GarageProUserRepository $garageProUserRepository
     * @param GarageFromDTOBuilder $garageBuilder
     * @param ProVehicleEditionService $proVehicleEditionService
     * @param GoogleMapsApiConnector $googleMapsApiConnector
     * @param MessageBus $eventBus
     */
    public function __construct(
        GarageRepository $garageRepository,
        GarageProUserRepository $garageProUserRepository,
        GarageFromDTOBuilder $garageBuilder,
        ProVehicleEditionService $proVehicleEditionService,
        GoogleMapsApiConnector $googleMapsApiConnector,
        MessageBus $eventBus
    )
    {
        $this->garageRepository = $garageRepository;
        $this->garageProUserRepository = $garageProUserRepository;
        $this->garageBuilder = $garageBuilder;
        $this->proVehicleEditionService = $proVehicleEditionService;
        $this->googleMapsApi = $googleMapsApiConnector;
        $this->eventBus = $eventBus;
    }

    /**
     * @param $user
     * @param Garage $garage
     * @return bool
     */
    public function canEdit($user, Garage $garage): bool
    {
        return $user instanceof CanBeGarageMember && $user->isMemberOfGarage($garage);
    }

    /**
     * @param GarageDTO $garageDTO
     * @param null|Garage $garage
     * @param CanBeGarageMember $creator
     * @return Garage
     * @throws AlreadyGarageMemberException|ExistingGarageException
     */
    public function editInformations(GarageDTO $garageDTO, ?Garage $garage, CanBeGarageMember $creator): Garage
    {
        $existingGarage = null;

        if (!empty($garageDTO->googlePlaceId) && ($garage == null || $garage->getGooglePlaceId() !== $garageDTO->googlePlaceId)) {
            $existingGarage = $this->garageRepository->findOneBy(['googlePlaceId' => $garageDTO->googlePlaceId]);
        }
        if ($existingGarage === null && ($garage == null || $garage->getName() !== $garageDTO->name)) {
            $existingGarage = $this->garageRepository->findOneBy(['name' => $garageDTO->name]);
        }

        if ($existingGarage != null) {
            if ($creator->isMemberOfGarage($existingGarage)) {
                throw new AlreadyGarageMemberException($existingGarage);
            } else {
                throw new ExistingGarageException($existingGarage);
            }
        } else {
            /** @var Garage $garage */
            $garage = $this->garageBuilder->buildFromDTO($garageDTO, $garage);

            if (!$creator->isMemberOfGarage($garage)) {
                $this->addMember($garage, $creator, true);
            }

            $garage = $this->garageRepository->update($garage);
            $this->eventBus->handle(new GarageUpdated($garage));

            return $garage;
        }
    }

    /**
     * @param Garage $garage
     * @param ProApplicationUser $proUser
     * @param boolean $isAdministrator
     * @return Garage
     */
    public function addMember(Garage $garage, ProApplicationUser $proUser, bool $isAdministrator = false): Garage
    {
        if (!in_array('ROLE_ADMIN', $proUser->getRoles())) {
            /** @var GarageProUser $garageProUser */
            $garageProUser = new GarageProUser($garage, $proUser, $isAdministrator ? GarageRole::GARAGE_ADMINISTRATOR() : GarageRole::GARAGE_MEMBER());
            if (!$isAdministrator) {
                $garageProUser->setRequestedAt(new \DateTime());
            }
            $garage->addMember($garageProUser);
            $proUser->addGarageMembership($garageProUser);
            $this->garageRepository->update($garage);
        }

        return $garage;
    }

    /**
     * @param Garage $garage
     * @param ProApplicationUser $proApplicationUser
     * @return Garage
     */
    public function removeMember(Garage $garage, ProApplicationUser $proApplicationUser)
    {
        /** @var GarageProUser $member */
        $member = $proApplicationUser->getMembershipByGarage($garage);
        if (null === $member) {
            throw new \InvalidArgumentException('User should be member of the garage');
        }
        $garage->removeMember($member);
        $this->garageProUserRepository->remove($member);
        $this->garageRepository->update($garage);

        return $garage;
    }

    /**
     * @param Garage $garage
     */
    public function remove(Garage $garage)
    {
        $this->proVehicleEditionService->deleteAllForGarage($garage);
        /** @var GarageProUser $member */
        foreach ($garage->getMembers() as $member) {
            $this->removeMember($garage, $member->getProUser());
        }

        $this->garageRepository->remove($garage);
    }

    /**
     * @param Garage $garage
     */
    public function getGooglePlaceDetails(Garage $garage)
    {
        if (!empty($garage->getGooglePlaceId())) {
            $googlePlaceDetails = $this->googleMapsApi->getPlaceDetails($garage->getGooglePlaceId());
            if ($googlePlaceDetails != null) {
                if (isset($googlePlaceDetails["rating"]) && $garage->getGoogleRating() !== $googlePlaceDetails["rating"]) {
                    $garage->setGoogleRating($googlePlaceDetails["rating"]);
                    $this->garageRepository->update($garage);
                    $this->eventBus->handle(new GarageUpdated($garage));
                }
            }
            return $googlePlaceDetails;
        }
        return null;
    }
}
