<?php

namespace AppBundle\Form\DTO;

use Symfony\Component\HttpFoundation\File\File;

class VehiclePictureDTO
{
    /** @var File */
    public $file;
    /** @var string */
    public $caption;
}
