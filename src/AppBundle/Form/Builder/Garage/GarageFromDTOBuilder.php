<?php

namespace AppBundle\Form\Builder\Garage;


use AppBundle\Doctrine\Repository\DoctrineGarageRepository;
use AppBundle\Form\DTO\GarageDTO;
use Wamcar\Garage\Garage;

class GarageFromDTOBuilder
{
    /**
     * @var DoctrineGarageRepository $garageRepository
     */
    private $garageRepository;

    /**
     * GarageFromDTOBuilder constructor.
     * @param DoctrineGarageRepository $garageRepository
     */
    public function __construct(
        DoctrineGarageRepository $garageRepository
    )
    {
        $this->garageRepository = $garageRepository;
    }

    /**
     * @param GarageDTO $dto
     * @param null|Garage $garage
     * @return Garage
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function buildFromDTO($dto, ?Garage $garage): Garage
    {
        if (null === $garage) {
            return $this->newGarageFromDto($dto);
        } else {
            return $this->editGarageFromDto($dto, $garage);
        }
    }

    /**
     * Create a new Garage from dto
     *
     * @param GarageDTO $dto
     * @return Garage
     */
    protected function newGarageFromDto($dto): Garage
    {
        if (!$dto instanceof GarageDTO) {
            throw new \InvalidArgumentException(
                sprintf(
                    "GarageFromDTOBuilder::buildFromDTO expects $dto argument to be an instance of '%s', '%s' given"),
                GarageDTO::class,
                get_class($dto)
            );
        }

        return new Garage(
            $dto->name,
            $dto->siren,
            $dto->openingHours,
            $dto->presentation,
            $dto->getAddress()
        );
    }

    /**
     * Edit a Garage from dto
     *
     * @param GarageDTO $dto
     * @param Garage $garage
     * @return Garage
     */
    protected function editGarageFromDto($dto, $garage): Garage
    {
        if (!$dto instanceof GarageDTO) {
            throw new \InvalidArgumentException(
                sprintf(
                    "GarageFromDTOBuilder::buildFromDTO expects $dto argument to be an instance of '%s', '%s' given"),
                GarageDTO::class,
                get_class($dto)
            );
        }

        $garage->setEmail($dto->email);
        $garage->setName($dto->name);
        $garage->setPhone($dto->phone);
        $garage->setSiren($dto->siren);
        $garage->setOpeningHours($dto->openingHours);
        $garage->setPresentation($dto->presentation);
        $garage->setBenefit($dto->benefit);
        $garage->setAddress($dto->getAddress());

        return $garage;
    }

}
