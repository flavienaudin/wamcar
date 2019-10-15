<?php


namespace AppBundle\Services\Picture;


use AppBundle\Doctrine\Entity\UserBanner;

class PathUserBanner extends BasePathPicture
{
    /**
     * @param UserBanner |null $userBanner
     * @param string $filter
     * @param null|string $firstname
     * @return string
     */
    public function getPath(?UserBanner $userBanner, string $filter): string
    {
        return $this->getUserBannerPath($userBanner, $filter, 'file', 'user_banner');
    }
}
