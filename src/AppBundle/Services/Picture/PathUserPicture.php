<?php


namespace AppBundle\Services\Picture;


use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Utils\AccentuationUtils;

class PathUserPicture extends BasePathPicture
{
    /**
     * @param UserPicture|null $userPicture
     * @param string $filter
     * @param null|string $firstname
     * @return string
     */
    public function getPath(?UserPicture $userPicture, string $filter, ?string $firstname): string
    {
        return $this->getUserPicturePath($userPicture, $filter, 'file', 'avatar', AccentuationUtils::remove($firstname));
    }
}
