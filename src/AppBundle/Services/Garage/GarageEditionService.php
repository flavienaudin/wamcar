<?php

namespace AppBundle\Services\Garage;

use AppBundle\Form\Builder\Garage\GarageFromDTOBuilder;
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
     * @param null|Garage $garage
     * @return Garage
     */
    public function editInformations(GarageDTO $garageDTO, ?Garage $garage): Garage
    {
        /** @var Garage $garage */
        $garage = $this->garageBuilder->buildFromDTO($garageDTO, $garage);

        $this->garageRepository->update($garage);

        return $garage;
    }
}
