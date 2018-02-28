<?php

namespace AppBundle\Doctrine\Entity;

use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\File\File;
use Wamcar\Vehicle\Picture;
use Wamcar\Vehicle\Vehicle;

abstract class VehiclePicture extends Picture implements ApplicationPicture
{
    use FileHolderTrait;

    /** @var string */
    private $id;
    /** @var integer */
    protected $position;

    /**
     * VehiclePicture constructor.
     * @param null $id
     * @param Vehicle $vehicle
     * @param File $file
     * @param string|null $caption
     * @param int|null $position
     */
    public function __construct($id = null, Vehicle $vehicle, File $file, string $caption = null, int $position = null)
    {
        $this->id = $id ?: Uuid::uuid4();
        $this->setFile($file);
        $this->position = $position;
        parent::__construct($vehicle, $caption);
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param null|string $caption
     */
    public function setCaption($caption): void
    {
        $this->caption = $caption;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }
}
