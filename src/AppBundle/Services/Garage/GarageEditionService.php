<?php

namespace AppBundle\Services\Garage;

use AppBundle\Doctrine\Entity\ApplicationGarage;
use AppBundle\Form\DTO\GarageDTO;
use Wamcar\Garage\GarageRepository;


class GarageEditionService
{
    /** @var GarageRepository  */
    private $garageRepository;

    /**
     * UserRepository constructor.
     *
     * @param GarageRepository $garageRepository
     */
    public function __construct(
        GarageRepository $garageRepository
    )
    {
        $this->garageRepository = $garageRepository;
    }

    /**
     * @param ApplicationGarage $garage
     * @param GarageDTO $garageDTO
     * @return ApplicationGarage
     */
    public function editInformations(ApplicationGarage $garage, GarageDTO $garageDTO): ApplicationGarage
    {
    }
}
