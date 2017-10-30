<?php

namespace AppBundle\Builder\Garage;


use AppBundle\Doctrine\Entity\ApplicationGarage;
use AppBundle\DTO\BuilderFromDTO;
use AppBundle\Form\DTO\GarageDTO;
use Wamcar\Garage\Garage;

class GarageFromDTOBuilder implements BuilderFromDTO
{

    /**
     * @param mixed $dto
     * @return Garage
     * @throws \InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    public function buildFromDTO($dto): Garage
    {
        return $this->buildNewGarageFromDto($dto);
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
            $dto->phone,
            $dto->email,
            $dto->openingHours,
            $dto->presentation,
            $dto->benefit,
            $dto->getAddress()
        );
    }

}
