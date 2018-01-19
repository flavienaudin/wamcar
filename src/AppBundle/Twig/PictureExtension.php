<?php


namespace AppBundle\Twig;

use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Doctrine\Entity\VehiclePicture;
use AppBundle\Services\Picture\PathGaragePicture;
use AppBundle\Services\Picture\PathUserPicture;
use AppBundle\Services\Picture\PathVehiclePicture;
use Twig\Extension\AbstractExtension;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\Garage\Garage;
use Wamcar\User\BaseUser;

class PictureExtension extends AbstractExtension
{
    /** @var UploaderHelper */
    protected $uploaderHelper;
    /** @var array */
    protected $placeholders;
    /** @var PathVehiclePicture */
    protected $pathVehiclePicture;
    /** @var PathGaragePicture */
    protected $pathGaragePicture;
    /** @var PathUserPicture */
    protected $pathUserPicture;


    /**
     * PictureExtension constructor.
     * @param UploaderHelper $uploaderHelper
     * @param array $placeholders
     * @param PathVehiclePicture $pathVehiclePicture
     * @param PathGaragePicture $pathGaragePicture
     * @param PathUserPicture $pathUserPicture
     */
    public function __construct(
        UploaderHelper $uploaderHelper,
        array $placeholders,
        PathVehiclePicture $pathVehiclePicture,
        PathGaragePicture $pathGaragePicture,
        PathUserPicture $pathUserPicture
    )
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->placeholders = $placeholders;
        $this->pathVehiclePicture = $pathVehiclePicture;
        $this->pathGaragePicture = $pathGaragePicture;
        $this->pathUserPicture = $pathUserPicture;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('avatar', array($this, 'avatarFilter')),
            new \Twig_SimpleFilter('banner', array($this, 'bannerFilter')),
            new \Twig_SimpleFilter('logo', array($this, 'logoFilter')),
            new \Twig_SimpleFilter('vehiclePicture', array($this, 'vehiclePictureFilter')),
            new \Twig_SimpleFilter('defaultVehiclePicture', array($this, 'defaultVehiclePictureFilter')),
            new \Twig_SimpleFilter('defaultAvatar', array($this, 'defaultAvatarFilter'))
        );
    }

    public function avatarFilter(?UserPicture $userPicture, string $filter)
    {
        return $this->pathUserPicture->getPath($userPicture, $filter);
    }

    public function defaultAvatarFilter(string $filter)
    {
        return $this->pathUserPicture->getPath(null, $filter);
    }

    public function bannerFilter(?Garage $garage, string $filter)
    {
        return $this->pathGaragePicture->getBannerPath($garage, $filter);
    }

    public function logoFilter(?Garage $garage, string $filter)
    {
        return $this->pathGaragePicture->getLogoPath($garage, $filter);
    }

    public function vehiclePictureFilter(?VehiclePicture $vehiclePicture, string $filter)
    {
        return $this->pathVehiclePicture->getPath($vehiclePicture, $filter);
    }

    public function defaultVehiclePictureFilter(string $filter)
    {
        return $this->pathVehiclePicture->getPath(null, $filter);
    }
}
