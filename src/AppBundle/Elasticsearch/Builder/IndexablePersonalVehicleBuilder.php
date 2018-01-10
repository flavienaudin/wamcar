<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use Symfony\Component\Routing\Router;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\Vehicle\PersonalVehicle;

class IndexablePersonalVehicleBuilder
{
    /** @var Router */
    private $router;
    /** @var UploaderHelper */
    private $uploaderHelper;

    public function __construct(Router $router, UploaderHelper $uploaderHelper)
    {
        $this->router = $router;
        $this->uploaderHelper = $uploaderHelper;
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
            $vehicle->getTransmission(),
            $vehicle->getFuelName(),
            $vehicle->getYears(),
            $vehicle->getMileage(),
            $vehicle->getCityName(),
            $vehicle->getCity()->getLatitude(),
            $vehicle->getCity()->getLongitude(),
            $vehicle->getCreatedAt(),
            $this->uploaderHelper->asset($vehicle->getMainPicture(), 'file'),
            $vehicle->getOwner()->getName(),
            $this->uploaderHelper->asset($vehicle->getOwner()->getAvatar(), 'file')
        );
    }

}
