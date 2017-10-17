<?php

namespace AppBundle\Form\DTO;

use Wamcar\Vehicle\Engine;
use Wamcar\Vehicle\Enum\Transmission;
use Wamcar\Vehicle\ModelVersion;

class VehicleDTO
{
    const DEFAULT_PICTURE_COUNT = 4;

    /** @var string */
    public $registrationNumber;
    /** @var VehicleIdentificationDTO */
    public $identification;
    /** @var VehiclePictureDTO[]|array */
    public $pictures;

    /**
     * VehicleDTO constructor.
     */
    public function __construct()
    {
        $this->pictures = array_map(function () {
            return new VehiclePictureDTO();
        }, range(1, self::DEFAULT_PICTURE_COUNT));
    }

    /**
     * @param VehiclePictureDTO $picture
     */
    public function addPicture(VehiclePictureDTO $picture): void
    {
        $this->pictures[] = $picture;
    }

    /**
     * @param VehiclePictureDTO $picture
     */
    public function removePicture(VehiclePictureDTO $picture): void
    {
        if (($key = array_search($picture, $this->pictures, true)) !== FALSE) {
            unset($this->pictures[$key]);
        }
    }

    /**
     * @return ModelVersion
     */
    public function getModelVersion(): ?ModelVersion
    {
        return $this->identification->getModelVersion();
    }

    /**
     * @return Transmission
     */
    public function getTransmission(): Transmission
    {
        return $this->identification->transmission;
    }
}
