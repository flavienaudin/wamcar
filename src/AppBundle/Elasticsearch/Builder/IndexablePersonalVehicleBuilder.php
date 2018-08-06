<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use AppBundle\Services\Picture\PathUserPicture;
use AppBundle\Services\Picture\PathVehiclePicture;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\Vehicle\PersonalVehicle;

class IndexablePersonalVehicleBuilder
{
    /** @var Router */
    private $router;
    /** @var PathVehiclePicture */
    private $pathVehiclePicture;
    /** @var PathUserPicture */
    private $pathUserPicture;

    /**
     * IndexablePersonalVehicleBuilder constructor.
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
     * @param PersonalVehicle $vehicle
     * @return IndexablePersonalVehicle
     */
    public function buildFromVehicle(PersonalVehicle $vehicle): IndexablePersonalVehicle
    {
        return new IndexablePersonalVehicle(
            $vehicle->getId(),
            $this->router->generate('front_vehicle_personal_detail', ['id' => $vehicle->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            $vehicle->getMake(),
            $vehicle->getName(),
            $vehicle->getModelName(),
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
            $this->pathVehiclePicture->getPath($vehicle->getMainPicture(), $vehicle->getMainPicture()?'vehicle_thumbnail':'vehicle_placeholder_thumbnail'),
            count($vehicle->getPictures()),
            $this->router->generate('front_view_user_info', ['id' => $vehicle->getOwner()->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            $vehicle->getSellerName() ?? '',
            $this->pathUserPicture->getPath($vehicle->getSellerAvatar(), 'user_mini_thumbnail'),
            count($vehicle->getPositiveLikes())
        );
    }

}
