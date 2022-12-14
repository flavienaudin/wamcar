<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use AppBundle\Services\Picture\PathVehiclePicture;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Wamcar\Vehicle\ProVehicle;

class IndexableProVehicleBuilder
{
    /** @var RouterInterface */
    private $router;
    /** @var PathVehiclePicture */
    private $pathVehiclePicture;

    /**
     * IndexableProVehicleBuilder constructor.
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
     * @param ProVehicle $vehicle
     * @return IndexableProVehicle
     */
    public function buildFromVehicle(ProVehicle $vehicle): IndexableProVehicle
    {
        return new IndexableProVehicle(
            $vehicle->getId(),
            $this->router->generate('front_vehicle_pro_detail', ['slug' => $vehicle->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL),
            strtoupper($vehicle->getMake()),
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
            $vehicle->getPrice(),
            $vehicle->getCreatedAt(),
            $this->pathVehiclePicture->getPath($vehicle->getMainPicture(), $vehicle->getMainPicture() ? 'vehicle_thumbnail' : 'vehicle_placeholder_thumbnail'),
            count($vehicle->getPictures()),
            $vehicle->getGarage() ? $vehicle->getGarage()->getId() : null,
            $vehicle->getDeletedAt(),
            $vehicle->getGarage() ? $vehicle->getGarage()->getGoogleRating() : null,
            count($vehicle->getPositiveLikes()),
            count($vehicle->getSuggestedSellers(false, null)) > 0
        );
    }

}
