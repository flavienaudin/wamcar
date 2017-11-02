<?php


namespace AppBundle\Services\Garage;


use AppBundle\Doctrine\Repository\DoctrineGarageRepository;

class GarageProvider
{
    /** @var DoctrineGarageRepository  */
    private $garageRepository;

    /**
     * GarageProvider constructor.
     * @param DoctrineGarageRepository $garageRepository
     */
    public function __construct(
        DoctrineGarageRepository $garageRepository
    )
    {
        $this->garageRepository = $garageRepository;
    }


    public function provideLatest(): array
    {
        return $this->garageRepository->getLatest();
    }

}
