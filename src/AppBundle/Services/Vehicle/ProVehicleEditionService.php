<?php

namespace AppBundle\Services\Vehicle;

use AppBundle\Api\DTO\VehicleDTO as ApiVehicleDTO;
use AppBundle\Api\EntityBuilder\ProVehicleBuilder as ApiVehicleBuilder;
use AppBundle\Doctrine\Entity\ProApplicationUser;
use AppBundle\Doctrine\Entity\ProVehiclePicture;
use AppBundle\Doctrine\Repository\DoctrineLikeProVehicleRepository;
use AppBundle\Exception\Vehicle\NewSellerToAssignNotFoundException;
use AppBundle\Form\DTO\ProVehicleDTO as FormVehicleDTO;
use AppBundle\Form\EntityBuilder\ProVehicleBuilder as FormVehicleBuilder;
use AppBundle\Services\Picture\PathVehiclePicture;
use Elastica\ResultSet;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageProUser;
use Wamcar\Garage\GarageRepository;
use Wamcar\User\BaseUser;
use Wamcar\User\Event\UserLikeVehicleEvent;
use Wamcar\User\ProLikeVehicle;
use Wamcar\User\ProUser;
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
    /** @var RouterInterface */
    private $router;
    /** @var TranslatorInterface */
    private $translator;
    /** @var PathVehiclePicture */
    private $pathVehiclePicture;

    /**
     * ProVehicleEditionService constructor.
     * @param ProVehicleRepository $vehicleRepository
     * @param GarageRepository $garageRepository
     * @param DoctrineLikeProVehicleRepository $likeProVehicleRepository
     * @param MessageBus $eventBus
     * @param RouterInterface $router
     * @param TranslatorInterface $translator
     * @param PathVehiclePicture $pathVehiclePicture
     */
    public function __construct(
        ProVehicleRepository $vehicleRepository,
        GarageRepository $garageRepository,
        DoctrineLikeProVehicleRepository $likeProVehicleRepository,
        MessageBus $eventBus, RouterInterface $router, TranslatorInterface $translator,
        PathVehiclePicture $pathVehiclePicture
    )
    {
        $this->vehicleRepository = $vehicleRepository;
        $this->garageRepository = $garageRepository;
        $this->likeProVehicleRepository = $likeProVehicleRepository;
        $this->eventBus = $eventBus;
        $this->router = $router;
        $this->translator = $translator;
        $this->pathVehiclePicture = $pathVehiclePicture;
        $this->vehicleBuilder = [
            ApiVehicleDTO::class => ApiVehicleBuilder::class,
            FormVehicleDTO::class => FormVehicleBuilder::class
        ];
    }

    /**
     * Retrieve ProVehicles from the given garage
     * @param Garage $garage
     * @param array $orderBy
     * @return array
     */
    public function getVehiclesByGarage(Garage $garage, array $orderBy = []): array
    {
        return $this->vehicleRepository->findAllForGarage($garage, $orderBy);
    }

    /**
     * Retrieve ProVehicles from the search result
     * @param ResultSet $searchResult
     * @return array
     */
    public function getVehiclesBySearchResult(ResultSet $searchResult): array
    {
        $results = array();
        $results['totalHits'] = $searchResult->getTotalHits();
        $results['hits'] = array();
        $ids = array();
        foreach ($searchResult->getResults() as $result) {
            $vehicle = $result->getData();
            $ids[] = $vehicle['id'];
        }
        if (count($ids) > 0) {
            $results['hits'] = $this->vehicleRepository->findByIds($ids);
        }
        return $results;
    }


    /**
     * @param ProUser $proUser
     * @param array Request params
     * @return array of ProVehicle
     */
    public function getProUserVehiclesForSalesDeclaration(ProUser $proUser, array $params): array
    {
        $vehicles = $this->vehicleRepository->getDeletedProVehiclesByRequest($proUser, $params);

        $vehiclesToDeclare = [
            "draw" => intval($params['draw']),
            "recordsTotal" => $vehicles['recordsTotalCount'],
            "recordsFiltered" => $vehicles['recordsFilteredCount'],
            "data" => []
        ];
        /** @var ProVehicle $vehicle */
        foreach ($vehicles['data'] as $vehicle) {
            $vehicleInfoForDataTable = [
                'control' => '<td><span class="icon-plus-circle no-margin"></span></td>',
                'image' => '<img src="' . $this->pathVehiclePicture->getPath($vehicle->getMainPicture(), 'vehicle_picture') . '" class="img-responsive" alt="' . $vehicle->getNAme() . '">',
                'vehicle' => $vehicle->getName() . '<br>' .
                    $vehicle->getPrice() . '€' . '<br>' .
                    '<a href="' . $this->router->generate('front_garage_view', ['slug' => $vehicle->getGarage()->getSlug()]) . '" target="_blank">' . $vehicle->getGarageName() . '</a>',
                'date' => $vehicle->getDeletedAt()->format("d/m/y"),
                'actions' => ($vehicle->getSaleDeclaration() != null ?
                    $this->translator->trans('vehicle.sale.sold_by', ['%sellerName%' => $vehicle->getSaleDeclaration()->getProUserSeller()->getFullName()]) :
                    '<a href="' . $this->router->generate('front_sale_declaration_new', ['vehicleId' => $vehicle->getId()], UrlGenerator::ABSOLUTE_URL) . '">' . $this->translator->trans('vehicle.sale.i_sold') . '</a>'
                )

            ];
            $vehiclesToDeclare['data'][] = $vehicleInfoForDataTable;
        }
        return $vehiclesToDeclare;
    }

    /**
     * @param CanBeProVehicle $proVehicleDTO
     * @param Garage $garage
     * @param ProUser|null $seller
     * @return ProVehicle
     */
    public function createInformations(CanBeProVehicle $proVehicleDTO, Garage $garage, ProUser $seller = null): ProVehicle
    {
        /** @var ProVehicle $proVehicle */
        $proVehicle = $this->vehicleBuilder[get_class($proVehicleDTO)]::newVehicleFromDTO($proVehicleDTO);
        $proVehicle->setGarage($garage);
        if ($seller == null) {
            // TODO Tirage aléatoire en attendant implémentation des règles
            $members = $garage->getAvailableSellers()->toArray();
            $seller = $members[array_rand($members)]->getProUser();
        }
        $proVehicle->setSeller($seller);

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
        if ($proVehicle->getGarage()->isOptionAdminSellers() === false) {
            // TODO Cette vérification est faite pour corriger le cas AutoBonPlan (Romane est admin et vendeuse)
            $availableSellers = $proVehicle->getGarage()->getAvailableSellers()->map(function (GarageProUser $memberShip) {
                return $memberShip->getProUser();
            });
            // Admins can't be sellers (The only one rule today)
            if (!$availableSellers->contains($proVehicle->getSeller())) {
                // The actual seller is an admin but it's not allowed by the garage option
                // TODO Tirage aléatoire en attendant implémentation des règles
                $members = $availableSellers->toArray();
                $seller = $members[array_rand($members)];
                $proVehicle->setSeller($seller);
            }
        }

        $this->vehicleRepository->update($proVehicle);
        $this->eventBus->handle(new ProVehicleUpdated($vehicle));

        return $vehicle;
    }

    /**
     * Assign the vehicle to another seller.
     * @param ProVehicle $proVehicle
     * @param ProApplicationUser|null $newSeller
     * @return ProVehicle
     * @throws \InvalidArgumentException|NewSellerToAssignNotFoundException
     */
    public function assignSeller(ProVehicle $proVehicle, ProApplicationUser $newSeller = null): ProVehicle
    {
        if ($newSeller == null) {
            // Search for a new seller among garage'sellers
            /** @var GarageProUser[] $members */
            $members = $proVehicle->getGarage()->getAvailableSellers()->toArray();
            do {
                $randIndex = array_rand($members);
                $newSeller = $members[$randIndex]->getProUser();
                if (!$proVehicle->getSeller()->is($newSeller)) {
                    break;
                }
                unset($members[$randIndex]);
            } while (count($members) > 0);

            if (count($members) == 0) {
                throw new NewSellerToAssignNotFoundException($proVehicle);
            }
        }
        if (!$newSeller->isMemberOfGarage($proVehicle->getGarage())) {
            throw new \InvalidArgumentException('flash.error.vehicle.assign_to_non_garage_member');
        }
        $proVehicle->setSeller($newSeller);
        $this->vehicleRepository->update($proVehicle);
        $this->eventBus->handle(new ProVehicleUpdated($proVehicle));
        return $proVehicle;
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
        // If Garage is softDeleted, then its vehicles are not retrieved and we want ALL vehicle to delete
        $proVehicleToDelete = $this->vehicleRepository->findAllForGarage($garage, null, $garage->getDeletedAt() != null);
        /** @var ProVehicle $proVehicle */
        foreach ($proVehicleToDelete as $proVehicle) {
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
     * @param $user
     * @param ProVehicle $vehicle
     * @return bool
     */
    public function canDeclareSale($user, ProVehicle $vehicle): bool
    {
        return $vehicle !== null && $vehicle->canDeclareSale($user);
    }

    /**
     * Create a new ProLikeVehicle with value to 1 or update the existing ProLikeVehicle with toggled value
     * @param BaseUser $user The user who likes
     * @param ProVehicle $vehicle The liked vehicle
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
        }
        $this->eventBus->handle(new ProVehicleUpdated($vehicle));
        $this->eventBus->handle(new UserLikeVehicleEvent($like));
    }

    /**
     * @param int Clear pictures of vehicles soft deleted for $months months
     * @param SymfonyStyle|null $io If in command context, to display information
     * @return array Results
     */
    public function clearSoftDeletedVehiclesPictures(int $months, ?SymfonyStyle $io = null): array
    {
        $proVehicles = $this->vehicleRepository->findSoftDeletedForXMonth($months);
        if ($io) {
            $io->progressStart(count($proVehicles));
        }

        $nbRemovedPictures['total'] = 0;
        array_walk($proVehicles, function (ProVehicle $proVehicle) use (&$nbRemovedPictures, $io) {
            $nbRemovedPictures['total'] += $proVehicle->keepNPicture(1);
            if ($io) {
                $io->progressAdvance();
            }
        });
        if ($io) {
            $io->progressFinish();
        }

        $this->vehicleRepository->saveBulk($proVehicles);
        return $nbRemovedPictures;
    }
}
