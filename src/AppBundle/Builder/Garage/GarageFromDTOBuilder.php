<?php

namespace AppBundle\Builder\Garage;


use AppBundle\Builder\BuilderFromDTO;
use AppBundle\Doctrine\Entity\ApplicationGarage;
use AppBundle\Doctrine\Repository\DoctrineGarageRepository;
use AppBundle\Form\DTO\GarageDTO;
use Wamcar\Garage\Garage;

class GarageFromDTOBuilder implements BuilderFromDTO
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
     * @return Garage
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function buildFromDTO($dto): Garage
    {
        if (null === $dto->id)
            return $this->buildNewGarageFromDto($dto);
        else
            return $this->buildEditGarageFromDto($dto);
    }

    /**
     * Create a new Garage from dto
     *
     * @param $dto
     * @return Garage
     */
    protected function buildNewGarageFromDto($dto): Garage
    {
        if (!$dto instanceof GarageDTO) {
            throw new \InvalidArgumentException(
                sprintf(
                    "GarageFromDTOBuilder::buildFromDTO expects $dto argument to be an instance of '%s', '%s' given"),
                GarageDTO::class,
                get_class($dto)
            );
        }

        return new ApplicationGarage(
            $dto->name,
            $dto->siren,
            $dto->openingHours,
            $dto->presentation,
            $dto->fillAddress()
        );
    }

    /**
     * Create a new Garage from dto
     *
     * @param $dto
     * @return Garage
     */
    protected function buildEditGarageFromDto($dto): Garage
    {
        if (!$dto instanceof GarageDTO) {
            throw new \InvalidArgumentException(
                sprintf(
                    "GarageFromDTOBuilder::buildFromDTO expects $dto argument to be an instance of '%s', '%s' given"),
                GarageDTO::class,
                get_class($dto)
            );
        }

        $applicationGarage = $this->garageRepository->findOne($dto->id);
        $applicationGarage->setEmail($dto->email);
        $applicationGarage->setName($dto->name);
        $applicationGarage->setPhone($dto->phone);
        $applicationGarage->setSiren($dto->siren);
        $applicationGarage->setOpeningHours($dto->openingHours);
        $applicationGarage->setPresentation($dto->presentation);
        $applicationGarage->setBenefit($dto->benefit);
        $applicationGarage->setAddress($dto->fillAddress());

        return $applicationGarage;
    }

}
