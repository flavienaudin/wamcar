<?php

namespace AppBundle\Services\Garage;

use AppBundle\Form\Builder\Garage\GarageFromDTOBuilder;
use AppBundle\Doctrine\Entity\ApplicationGarage;
use AppBundle\Form\DTO\GarageDTO;
use Wamcar\Garage\Garage;
use Wamcar\Garage\GarageRepository;


class GarageEditionService
{
    /** @var GarageRepository  */
    private $garageRepository;
    /** @var GarageFromDTOBuilder  */
    private $garageBuilder;

    /**
     * GarageEditionService constructor.
     * @param GarageRepository $garageRepository
     * @param GarageFromDTOBuilder $garageBuilder
     */
    public function __construct(
        GarageRepository $garageRepository,
        GarageFromDTOBuilder $garageBuilder
    )
    {
        $this->garageRepository = $garageRepository;
        $this->garageBuilder = $garageBuilder;
    }

    /**
     * @param GarageDTO $garageDTO
     * @param null|ApplicationGarage $garage
     * @return ApplicationGarage
     */
    public function editInformations(GarageDTO $garageDTO, ?ApplicationGarage $garage): ApplicationGarage
    {
        /** @var ApplicationGarage $garage */
        $garage = $this->garageBuilder->buildFromDTO($garageDTO, $garage);

        $this->garageRepository->update($garage);

        return $garage;
    }
}
