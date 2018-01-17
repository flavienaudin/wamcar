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
        return $this->getPicturePath($garage ? $garage->getBanner() : null, $filter, 'file', 'banner');
    }

    /**
     * @param null|Garage $garage
     * @param string $filter
     * @return string
     */
    public function getLogoPath(?Garage $garage, string $filter): string
    {

        return $this->getPicturePath($garage ? $garage->getLogo() : null, $filter, 'file', 'logo');
    }

}
