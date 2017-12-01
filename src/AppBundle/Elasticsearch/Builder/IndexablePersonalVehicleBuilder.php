<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use Symfony\Component\Routing\Router;
use Wamcar\Vehicle\PersonalVehicle;

class IndexablePersonalVehicleBuilder
{
    /** @var Router */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param PersonalVehicle $vehicle
     * @return IndexablePersonalVehicle
     */
    public function buildFromVehicle(PersonalVehicle $vehicle): IndexablePersonalVehicle
    {
        //TODO: correct the link of the detail url
        return new IndexablePersonalVehicle(
            $vehicle->getId(),
            $this->router->generate('front_vehicle_pro_add'),
            $vehicle->getMake(),
            $vehicle->getModelName(),
            $vehicle->getModelVersionName(),
            $vehicle->getEngineName(),
            $vehicle->getYears(),
            $vehicle->getMileage(),
            $vehicle->getCityName(),
            $vehicle->getCity()->getLatitude(),
            $vehicle->getCity()->getLongitude(),
            $vehicle->getCreatedAt(),
            '/assets/images/placeholders/vehicle/vehicle-default.jpg',
            'Particulier particulier',
            '/assets/images/placeholders/user/user.png'
        );
    }

}
