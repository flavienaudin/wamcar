<?php

namespace AppBundle\Form\DTO;

use AppBundle\Doctrine\Entity\VehiclePicture;
use Symfony\Component\HttpFoundation\File\File;

class VehiclePictureDTO
{
    /** @var string */
    public $id;
    /** @var File */
    public $file;
    /** @var string */
    public $caption;
    /** @var VehiclePicture */
    public $realPicture;
    /** @var bool */
    public $isRemoved;

    /**
     * @param VehiclePicture $picture
     * @return self
     */
    public static function buildFromPicture(VehiclePicture $picture)
    {
        $dto = new self();
        $dto->id = $picture->getId();
        $dto->file = $picture->getFile();
        $dto->caption = $picture->getCaption();
        $dto->realPicture = $picture;


        return $dto;
    }
}
