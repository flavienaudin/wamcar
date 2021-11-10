<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use AppBundle\Services\Picture\PathVehiclePicture;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Wamcar\Vehicle\PersonalVehicle;

class IndexablePersonalVehicleBuilder
{
    /** @var RouterInterface */
    private $router;
    /** @var PathVehiclePicture */
    private $pathVehiclePicture;

    /**
     * IndexablePersonalVehicleBuilder constructor.
     * @param RouterInterface $router
     * @param PathVehiclePicture $pathVehiclePicture
     */
    public function __construct(
        RouterInterface $router,
        PathVehiclePicture $pathVehiclePicture
    )
    {
        $this->router = $router;
        $this->pathVehiclePicture = $pathVehiclePicture;
    }

    /**
     * @param PersonalVehicle $vehicle
     * @return IndexablePersonalVehicle
     */
    public function buildFromVehicle(PersonalVehicle $vehicle): IndexablePersonalVehicle
    {
        return new IndexablePersonalVehicle(
            $vehicle->getId(),
            $this->router->generate('front_vehicle_personal_detail', ['slug' => $vehicle->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
            $vehicle->getMake(),
            $vehicle->getModelName(),
            null,
            $vehicle->getEngineName(),
            $vehicle->getTransmission(),
            $vehicle->getFuelName(),
            $vehicle->getAdditionalInformation(),
            $vehicle->getYears(),
            $vehicle->getMileage(),
            $vehicle->getCityName(),
            $vehicle->getLatitude(),
            $vehicle->getLongitude(),
            $vehicle->getCreatedAt(),
            $vehicle->getDeletedAt(),
            $this->pathVehiclePicture->getPath($vehicle->getMainPicture(), $vehicle->getMainPicture() ? 'vehicle_thumbnail' : 'vehicle_placeholder_thumbnail'),
            $vehicle->getNbPictures(),
            $vehicle->getOwner()->getId(),
            count($vehicle->getPositiveLikes())
        );
    }

}
