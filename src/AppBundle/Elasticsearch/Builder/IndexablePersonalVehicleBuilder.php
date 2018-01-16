<?php

namespace AppBundle\Elasticsearch\Builder;

use AppBundle\Elasticsearch\Type\IndexablePersonalVehicle;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        return new IndexablePersonalVehicle(
            $vehicle->getId(),
            $this->router->generate('front_vehicle_personal_detail', ['id' => $vehicle->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
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
            $vehicle->getCreatedAt(),
            $vehicle->getDeletedAt(),
            $vehicle->getMainPicture() ? $this->uploaderHelper->asset($vehicle->getMainPicture(), 'file') : '',
            count($vehicle->getPictures()),
            $this->router->generate('front_view_user_info', ['id' => $vehicle->getOwner()->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            $vehicle->getOwner()->getName() ?? '',
            $vehicle->getOwner()->getAvatar() ? $this->uploaderHelper->asset($vehicle->getOwner()->getAvatar(), 'file') : '',
            $vehicle->getOwner()->getProject()
        );
    }

}
