<?php

namespace AppBundle\Form\DTO;

use Symfony\Component\HttpFoundation\File\File;
use Wamcar\Vehicle\Picture;

class VehiclePictureDTO
{
    /** @var File */
    public $file;
    /** @var string */
    public $caption;

    /**
     * @param Picture $picture
     * @return self
     */
    public static function buildFromPicture(Picture $picture)
    {
        $dto = new self();
        $dto->file = $picture->getFile();
        $dto->caption = $picture->getCaption();

        return $dto;
    }
}
