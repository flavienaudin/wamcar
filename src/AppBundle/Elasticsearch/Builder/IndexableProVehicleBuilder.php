<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Elasticsearch\Type\IndexableProVehicle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\Vehicle\ProVehicle;

class IndexableProVehicleBuilder
{
    /** @var Router */
    private $router;
    /** @var UploaderHelper */
    private $uploaderHelper;
    /** @var string */
    private $avatarPlaceholder;
    /** @var string */
    private $vehiclePicturePlaceholder;

    /**
     * IndexableProVehicleBuilder constructor.
     * @param Router $router
     * @param UploaderHelper $uploaderHelper
     * @param $picturePlaceholders
     */
    public function __construct(Router $router, UploaderHelper $uploaderHelper, array $picturePlaceholders)
    {
        $this->router = $router;
        $this->uploaderHelper = $uploaderHelper;
        $this->avatarPlaceholder = $picturePlaceholders['avatar'];
        $this->vehiclePicturePlaceholder = $picturePlaceholders['vehicle'];
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
            $vehicle->getMake(),
            $vehicle->getModelName(),
            $vehicle->getModelVersionName(),
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
            count($vehicle->getPictures()) > 0 ? $this->uploaderHelper->asset($vehicle->getMainPicture(), 'file') : $this->vehiclePicturePlaceholder,
            count($vehicle->getPictures()),
            $this->router->generate('front_view_user_info', ['id' => $vehicle->getSeller()->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            $vehicle->getSellerName() ?? '',
            $vehicle->getSellerAvatar() ? $this->uploaderHelper->asset($vehicle->getSellerAvatar(), 'file') : $this->avatarPlaceholder,
            $vehicle->getDeletedAt()
        );
    }

}
