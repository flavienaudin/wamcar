<?php

namespace AppBundle\Form\DTO;

class VehicleDTO
{
    const DEFAULT_PICTURE_COUNT = 4;

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

}
