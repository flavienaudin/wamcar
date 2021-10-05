<?php

namespace AppBundle\Form\DTO;

use Symfony\Component\HttpFoundation\File\File;

class VideoProjectPictureDTO
{
    /** @var File */
    public $file;
    /** @var bool */
    public $isRemoved;

    public function __construct(?File $file)
    {
        $this->file = $file;
    }
}
