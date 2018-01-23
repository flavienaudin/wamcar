<?php

namespace AppBundle\Api\DTO;
use AppBundle\Services\Vehicle\CanBeProVehicle;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Wamcar\Vehicle\ProVehicle;
use Wamcar\Vehicle\Vehicle;

/**
 * @SWG\Definition(
 *   definition="VehicleShort",
 *   type="object"
 * )
 */
final class VehicleShortDTO
{
    /** @SWG\Property(type="integer", format="int64") */
    public $id;
    /** @SWG\Property(type="string", format="date") */
    public $updatedDate;

    /**
     * @param ProVehicle $proVehicle
     * @return VehicleShortDTO
     */
    public static function createFromProVehicle(ProVehicle $proVehicle): self
    {
        try {
            $vehicleDto = new self();

            $vehicleDto->id = $proVehicle->getReference();
            $vehicleDto->updatedDate = $proVehicle->getUpdatedAt()->format('Y-m-d\TH:i:sP');

            return $vehicleDto;
        }
        catch(\Exception $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
    }
}
