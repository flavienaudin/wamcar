<?php

namespace AppBundle\Api\ResponseBuilder;


use Wamcar\Vehicle\ProVehicle;

class JsonBuilder
{
    /**
     * @param ProVehicle $vehicle
     * @return array
     */
    public static function jsonFromVehicle(ProVehicle $vehicle): array
    {

        return [
            'id' => $vehicle->getReference(),
            'updatedDate' => $vehicle->getUpdatedAt()->format('Y-m-d\TH:i:sP')
        ];
    }

}
