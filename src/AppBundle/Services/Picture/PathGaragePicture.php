<?php


namespace AppBundle\Services\Picture;


use AppBundle\Doctrine\Entity\VehiclePicture;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\Garage\Garage;

class PathGaragePicture extends BasePathPicture
{
    /**
     * @param null|Garage $garage
     * @param string $filter
     * @return string
     */
    public function getBannerPath(?Garage $garage, string $filter): string
    {
        $picturePath = $garage->getBanner() ? $this->uploaderHelper->asset($garage->getBanner(), 'file'): $this->placeholders['banner'];

        return $this->imagineCacheManager->getBrowserPath($picturePath, $filter);
    }

    /**
     * @param null|Garage $garage
     * @param string $filter
     * @return string
     */
    public function getLogoPath(?Garage $garage, string $filter): string
    {
        $picturePath = $garage->getLogo() ? $this->uploaderHelper->asset($garage->getLogo(), 'file'): $this->placeholders['logo'];

        return $this->imagineCacheManager->getBrowserPath($picturePath, $filter);
    }

}
