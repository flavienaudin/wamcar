<?php

namespace AppBundle\Doctrine\Entity;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Wamcar\Garage\Garage;
use Wamcar\Garage\Picture;

abstract class GaragePicture extends Picture implements FileHolder
{
    use FileHolderTrait;

    /** @var string */
    private $id;

    /**
     * VehiclePicture constructor.
     * @param null|File $file
     */
    public function __construct(Garage $garage, ?File $file)
    {
        $this->id = Uuid::uuid4();
        $this->setFile($file);
        parent::__construct($garage);
    }
}
