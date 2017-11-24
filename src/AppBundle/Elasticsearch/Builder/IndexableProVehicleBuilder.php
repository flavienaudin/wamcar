<?php

namespace AppBundle\Elasticsearch\Type;

use Symfony\Component\Routing\Router;
use Wamcar\Vehicle\ProVehicle;

class IndexableProVehicleBuilder
{
    /** @var Router */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param ProVehicle $vehicle
     * @return IndexableProVehicle
     */
    public function buildFromVehicle(ProVehicle $vehicle): IndexableProVehicle
    {
        return new IndexableProVehicle(
            $vehicle->getId(),
            $this->router->generate('front_vehicle_pro_add'),
            $vehicle->getMake(),
            $vehicle->getModelName(),
            $vehicle->getModelVersionName(),
            $vehicle->getEngineName()
        );
    }

}
