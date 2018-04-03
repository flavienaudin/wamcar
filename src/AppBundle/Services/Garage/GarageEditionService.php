<?php

namespace AppBundle\Services\Garage;

use AppBundle\Doctrine\Entity\ApplicationGarage;
use AppBundle\Form\Builder\Garage\GarageFromDTOBuilder;
use AppBundle\Form\DTO\GarageDTO;
use AppBundle\Services\User\CanBeGarageMember;
use AppBundle\Services\Vehicle\ProVehicleEditionService;
use GoogleApi\GoogleMapsApiConnector;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Event\GarageUpdated;
use Wamcar\Garage\Garage;
use AppBundle\Doctrine\Entity\ProApplicationUser;
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
     */
    public function editInformations(GarageDTO $garageDTO, ?Garage $garage, CanBeGarageMember $creator): Garage
    {
        /** @var Garage $garage */
        $garage = $this->garageBuilder->buildFromDTO($garageDTO, $garage);

        if (!$creator->isMemberOfGarage($garage)) {
            $this->addMember($garage, $creator);
        }

        $garage = $this->garageRepository->update($garage);

        return $garage;
    }

    /**
     * @param Garage $garage
     * @param ProApplicationUser $proApplicationUser
     * @return Garage
     */
    public function addMember(Garage $garage, ProApplicationUser $proApplicationUser): Garage
    {
        /** @var GarageProUser $garageProUser */
        if (!in_array('ROLE_ADMIN', $proApplicationUser->getRoles())) {
            $garageProUser = new GarageProUser($garage, $proApplicationUser);
            $garage->addMember($garageProUser);
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
                if(isset($googlePlaceDetails["rating"]) && $garage->getGoogleRating() !== $googlePlaceDetails["rating"]){
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
