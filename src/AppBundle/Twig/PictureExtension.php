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
            new \Twig_SimpleFilter('defaultAvatar', array($this, 'defaultAvatarFilter')),
            new \Twig_SimpleFilter('defaultBanner', array($this, 'defaultBannerFilter')),
            new \Twig_SimpleFilter('defaultLogo', array($this, 'defaultLogoFilter')),
            new \Twig_SimpleFilter('defaultVehicleFormPicture', array($this, 'defaultVehicleFormPictureFilter'))
        );
    }

    public function avatarFilter(?UserPicture $userPicture, string $filter, string $firstname = null)
    {
        return $this->pathUserPicture->getPath($userPicture, $filter, $firstname);
    }

    public function defaultAvatarFilter(string $filter)
    {
        return $this->pathUserPicture->getPath(null, $filter, null);
    }

    public function bannerFilter(?Garage $garage, string $filter)
    {
        return $this->pathGaragePicture->getBannerPath($garage, $filter);
    }

    public function defaultBannerFilter(string $filter)
    {
        return $this->pathGaragePicture->getPicturePathPlaceholder($filter, 'banner');
    }

    public function logoFilter(?Garage $garage, string $filter)
    {
        return $this->pathGaragePicture->getLogoPath($garage, $filter);
    }

    public function defaultLogoFilter(string $filter)
    {
        return $this->pathGaragePicture->getPicturePathPlaceholder($filter, 'logo');
    }

    public function vehiclePictureFilter(?VehiclePicture $vehiclePicture, string $filter)
    {
        return $this->pathVehiclePicture->getPath($vehiclePicture, $filter);
    }

    public function defaultVehiclePictureFilter(string $filter)
    {
        return $this->pathVehiclePicture->getPath(null, $filter);
    }

    public function defaultVehicleFormPictureFilter(?string $filter, $index)
    {
        return $this->pathVehiclePicture->getFormPathPlaceholder($filter, $index);
    }
}
