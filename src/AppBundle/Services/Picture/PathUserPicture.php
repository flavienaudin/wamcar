<?php


namespace AppBundle\Services\Picture;


use AppBundle\Doctrine\Entity\UserPicture;

class PathUserPicture extends BasePathPicture
{
    /**
     * @param UserPicture|null $userPicture
     * @param string $filter
     * @return string
     */
    public function getPath(?UserPicture $userPicture, string $filter): string
    {
        return $this->getPicturePath($userPicture, $filter, 'file', 'avatar');
    }
}
