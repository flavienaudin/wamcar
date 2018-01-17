<?php

namespace AppBundle\Form\DTO;

use Symfony\Component\HttpFoundation\File\File;

class GaragePictureDTO
{
    /** @var File */
    public $file;
    /** @var bool */
    public $isRemoved;

    public function __construct(?File $file = null)
    {
        $this->file = $file;
    }
}
