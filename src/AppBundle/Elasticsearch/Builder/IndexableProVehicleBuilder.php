<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use AppBundle\Services\Picture\PathUserPicture;
use AppBundle\Services\Picture\PathVehiclePicture;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\Vehicle\ProVehicle;

class IndexableProVehicleBuilder
{
    /** @var Router */
    private $router;
    /** @var PathVehiclePicture */
    private $pathVehiclePicture;
    /** @var PathUserPicture */
    private $pathUserPicture;

    /**
     * IndexableProVehicleBuilder constructor.
     * @param Router $router
     * @param PathVehiclePicture $pathVehiclePicture
     * @param PathUserPicture $pathUserPicture
     */
    public function __construct(
        Router $router,
        PathVehiclePicture $pathVehiclePicture,
        PathUserPicture $pathUserPicture
    )
    {
        $this->router = $router;
        $this->pathVehiclePicture = $pathVehiclePicture;
        $this->pathUserPicture = $pathUserPicture;
    }

    /**
     * @param ProVehicle $vehicle
     * @return IndexableProVehicle
     */
    public function buildFromVehicle(ProVehicle $vehicle): IndexableProVehicle
    {
        return new IndexableProVehicle(
            $vehicle->getId(),
            $this->router->generate('front_vehicle_pro_detail', ['id' => $vehicle->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            strtoupper($vehicle->getMake()),
            $vehicle->getName(),
            $vehicle->getModelName(),
            $vehicle->getEngineName(),
            $vehicle->getTransmission(),
            $vehicle->getFuelName(),
            $vehicle->getYears(),
            $vehicle->getMileage(),
            $vehicle->getCityName(),
            $vehicle->getLatitude(),
            $vehicle->getLongitude(),
            $vehicle->getPrice(),
            $vehicle->getCreatedAt(),
            $this->pathVehiclePicture->getPath($vehicle->getMainPicture(), $vehicle->getMainPicture()?'vehicle_thumbnail':'vehicle_placeholder_thumbnail'),
            count($vehicle->getPictures()),
            $this->router->generate('front_view_user_info', ['id' => $vehicle->getSeller()->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            $vehicle->getSellerName() ?? '',
            $vehicle->getGarage() ? $this->router->generate('front_garage_view', ['id' => $vehicle->getGarage()->getId()], UrlGeneratorInterface::ABSOLUTE_URL) : '',
            $vehicle->getGarageName() ?? '',
            $this->pathUserPicture->getPath($vehicle->getSellerAvatar(), 'user_mini_thumbnail'),
            $vehicle->getDeletedAt(),
            $vehicle->getGarage() ? $vehicle->getGarage()->getGoogleRating():null,
            count($vehicle->getPositiveLikes())
        );
    }

}
