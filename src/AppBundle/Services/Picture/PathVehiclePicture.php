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
        $picturePath = $vehiclePicture ? $this->uploaderHelper->asset($vehiclePicture, 'file'): $this->placeholders['vehicle'];

        return $this->imagineCacheManager->getBrowserPath($picturePath, $filter);
    }

}
