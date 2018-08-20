<?php

namespace AppBundle\Services\Vehicle;

use AppBundle\Api\DTO\VehicleDTO as ApiVehicleDTO;
use AppBundle\Api\EntityBuilder\ProVehicleBuilder as ApiVehicleBuilder;
use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Doctrine\Repository\DoctrineLikeProVehicleRepository;
use AppBundle\Form\DTO\ProVehicleDTO as FormVehicleDTO;
use AppBundle\Form\EntityBuilder\ProVehicleBuilder as FormVehicleBuilder;
use Doctrine\Common\Collections\Criteria;
use Novaway\ElasticsearchClient\Query\Result;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;
use Wamcar\User\BaseUser;
use Wamcar\User\ProLikeVehicle;
use Wamcar\Vehicle\Event\ProVehicleCreated;
use Wamcar\Vehicle\Event\ProVehicleRemoved;
use Wamcar\Vehicle\Event\ProVehicleUpdated;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\ProVehicleRepository;


class ProVehicleEditionService
{
    /** @var ProVehicleRepository */
    private $vehicleRepository;
    /** @var GarageRepository */
    private $garageRepository;
    /** @var array */
    private $vehicleBuilder;
    /** @var DoctrineLikeProVehicleRepository */
    private $likeProVehicleRepository;
    /** @var MessageBus */
    private $eventBus;


    /**
     * ProVehicleEditionService constructor.
     * @param ProVehicleRepository $vehicleRepository
     * @param GarageRepository $garageRepository
     * @param DoctrineLikeProVehicleRepository $likeProVehicleRepository
     * @param MessageBus $eventBus
     */
    public function __construct(
        ProVehicleRepository $vehicleRepository,
        GarageRepository $garageRepository,
        DoctrineLikeProVehicleRepository $likeProVehicleRepository,
        MessageBus $eventBus
    )
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->garageRepository = $garageRepository;
        $this->likeProVehicleRepository = $likeProVehicleRepository;
        $this->eventBus = $eventBus;
        $this->vehicleBuilder = [
            ApiVehicleDTO::class => ApiVehicleBuilder::class,
            FormVehicleDTO::class => FormVehicleBuilder::class
        ];
    }

    /**
     * Retrieve ProVehicles from the search result
     * @param Result $searchResult
     * @param array $orderBy
     * @return array
     */
    public function getVehiclesByGarage(Garage $garage, array $orderBy = []): array
    {
        return $this->vehicleRepository->getByGarage($garage, array_merge($orderBy, ['createdAt' => Criteria::DESC]))->toArray();
    }

    /**
     * Retrieve ProVehicles from the search result
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
        if (count($ids) > 0) {
            $result['hits'] = $this->vehicleRepository->findByIds($ids);
        }
        return $result;
    }

    /**
     * @param CanBeProVehicle $proVehicleDTO
     * @param Garage $garage
     * @return ProVehicle
     */
    public function createInformations(CanBeProVehicle $proVehicleDTO, Garage $garage): ProVehicle
    {
        /** @var ProVehicle $proVehicle */
        $proVehicle = $this->vehicleBuilder[get_class($proVehicleDTO)]::newVehicleFromDTO($proVehicleDTO);
        $proVehicle->setGarage($garage);

        if (!$garage->hasVehicle($proVehicle)) {
            $garage->addProVehicle($proVehicle);
            $this->garageRepository->update($garage);
        }

        $this->vehicleRepository->add($proVehicle);
        $this->eventBus->handle(new ProVehicleCreated($proVehicle));

        return $proVehicle;
    }

    /**
     * @param CanBeProVehicle $proVehicleDTO
     * @param ProVehicle $vehicle
     * @return ProVehicle
     */
    public function updateInformations(CanBeProVehicle $proVehicleDTO, ProVehicle $vehicle): ProVehicle
    {
        /** @var ProVehicle $proVehicle */
        $proVehicle = $this->vehicleBuilder[get_class($proVehicleDTO)]::editVehicleFromDTO($proVehicleDTO, $vehicle);

        $this->vehicleRepository->update($proVehicle);
        $this->eventBus->handle(new ProVehicleUpdated($vehicle));

        return $vehicle;
    }

    /**
     * @param array|ProVehiclePicture[] $pictures
     * @param ProVehicle $vehicle
     * @return ProVehicle
     */
    public function addPictures(array $pictures, ProVehicle $vehicle): ProVehicle
    {
        foreach ($pictures as $picture) {
            $vehicle->addPicture($picture);
        }

        $this->vehicleRepository->update($vehicle);
        $this->eventBus->handle(new ProVehicleUpdated($vehicle));

        return $vehicle;
    }

    /**
     * @param ProVehicle $vehicle
     * @return ProVehicle
     */
    public function removePictures(ProVehicle $vehicle): ProVehicle
    {
        $vehicle->clearPictures();
        $this->vehicleRepository->update($vehicle);
        $this->eventBus->handle(new ProVehicleUpdated($vehicle));

        return $vehicle;
    }

    /**
     * @param ProVehicle $proVehicle
     */
    public function deleteVehicle(ProVehicle $proVehicle): void
    {
        $this->vehicleRepository->remove($proVehicle);
        $this->eventBus->handle(new ProVehicleRemoved($proVehicle));
    }

    /**
     * Supprime les véhicules du garage
     * @param Garage $garage
     * @return int Nombre de véhicules supprimés
     */
    public function deleteAllForGarage(Garage $garage): int
    {
        $nbProVehicles = 0;
        /** @var ProVehicle $proVehicle */
        foreach ($garage->getProVehicles() as $proVehicle) {
            $this->deleteVehicle($proVehicle);
            $nbProVehicles++;
        }
        return $nbProVehicles;
    }

    /**
     * @param $user
     * @param ProVehicle $vehicle
     * @return bool
     */
    public function canEdit($user, ProVehicle $vehicle): bool
    {
        return $vehicle !== null && $vehicle->canEditMe($user);
    }

    /**
     * Create a new ProLikeVehicle with value to 1 or update the existing ProLikeVehicle with toggled value
     */
    public function userLikesVehicle(BaseUser $user, ProVehicle $vehicle)
    {
        $like = $this->likeProVehicleRepository->findOneByUserAndVehicle($user, $vehicle);
        if ($like === null) {
            $like = new ProLikeVehicle($user, $vehicle, 1);
            $this->likeProVehicleRepository->add($like);
        } else {
            if ($like->getValue() === 1) {
                $like->setValue(0);
            } elseif ($like->getValue() === 0) {
                $like->setValue(1);
            }
            $this->likeProVehicleRepository->update($like);
            $this->eventBus->handle(new ProVehicleUpdated($vehicle));
        }
    }
}
