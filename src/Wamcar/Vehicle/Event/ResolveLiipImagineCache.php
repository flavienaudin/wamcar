<?php

namespace Wamcar\Vehicle\Event;


use AppBundle\Doctrine\Entity\VehiclePicture;
use AppBundle\Services\Picture\PathVehiclePicture;

class ResolveLiipImagineCache implements VehicleEventHandler
{
    /** @var PathVehiclePicture */
    private $pathVehiclePicture;

    /**
     * ResolveLiipImagineCache constructor.
     * @param PathVehiclePicture $pathVehiclePicture
     */
    public function __construct(PathVehiclePicture $pathVehiclePicture)
    {
        $this->pathVehiclePicture = $pathVehiclePicture;
    }


    /**
     * Génère le cache des images avec les filtres appliqués.
     * @inheritdoc()
     */
    public function notify(VehicleEvent $event)
    {
        $vehicle = $event->getVehicle();
        /** @var VehiclePicture $picture */
        foreach ($vehicle->getPictures() as $picture) {
            $this->pathVehiclePicture->getPath($picture, 'vehicle_picture');
            $this->pathVehiclePicture->getPath($picture, 'vehicle_mini_thumbnail');
            $this->pathVehiclePicture->getPath($picture, 'vehicle_thumbnail');
        }
    }

}