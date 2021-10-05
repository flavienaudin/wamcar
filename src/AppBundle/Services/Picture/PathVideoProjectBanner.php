<?php


namespace AppBundle\Services\Picture;


use AppBundle\Doctrine\Entity\VideoProjectBanner;

class PathVideoProjectBanner extends BasePathPicture
{
    /**
     * @param VideoProjectBanner|null $videoProjectBanner
     * @param string $filter
     * @return string
     */
    public function getPath(?VideoProjectBanner $videoProjectBanner, string $filter): string
    {
        return $this->getVideoProjectBannerPath($videoProjectBanner, $filter, 'file', 'videoproject_banner');
    }
}
