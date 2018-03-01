<?php


namespace AppBundle\Services\Picture;


use AppBundle\Doctrine\Entity\VehiclePicture;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

class PathVehiclePicture extends BasePathPicture
{
    /**
     * @param null|VehiclePicture $vehiclePicture
     * @param string $filter
     * @return string
     */
    public function getPath(?VehiclePicture $vehiclePicture, string $filter): string
    {
        return $this->getPicturePath($vehiclePicture, $filter, 'file', 'vehicle');
    }

    /**
     * @param string $filter
     * @param $index
     * @return null|string
     */
    public function getFormPathPlaceholder(string $filter, $index): ?string
    {
        return $this->getFormPicturePathPlaceholder($filter, $index);
    }

}
