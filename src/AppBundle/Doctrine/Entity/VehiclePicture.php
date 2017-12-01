<?php

namespace AppBundle\Doctrine\Entity;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Wamcar\Vehicle\Picture;
use Wamcar\Vehicle\Vehicle;

abstract class VehiclePicture extends Picture implements FileHolder
{
    use FileHolderTrait;

    /** @var string */
    private $id;

    /**
     * VehiclePicture constructor.
     * @param Vehicle $vehicle
     * @param File $file
     * @param string|null $caption
     */
    public function __construct(Vehicle $vehicle, File $file, string $caption = null)
    {
        $this->id = Uuid::uuid4();
        $this->setFile($file);
        parent::__construct($vehicle, $caption);
    }
}
