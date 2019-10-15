<?php


namespace AppBundle\Twig;

use AppBundle\Doctrine\Entity\UserBanner;
use AppBundle\Doctrine\Entity\UserPicture;
use AppBundle\Doctrine\Entity\VehiclePicture;
use AppBundle\Services\Picture\PathGaragePicture;
use AppBundle\Services\Picture\PathUserBanner;
use AppBundle\Services\Picture\PathUserPicture;
use AppBundle\Services\Picture\PathVehiclePicture;
use AppBundle\Utils\AccentuationUtils;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\Garage\Garage;

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
    /** @var PathUserBanner */
    protected $pathUserBanner;


    /**
     * PictureExtension constructor.
     * @param UploaderHelper $uploaderHelper
     * @param array $placeholders
     * @param PathVehiclePicture $pathVehiclePicture
     * @param PathGaragePicture $pathGaragePicture
     * @param PathUserPicture $pathUserPicture
     * @param PathUserBanner $pathUserBanner
     */
    public function __construct(
        UploaderHelper $uploaderHelper,
        array $placeholders,
        PathVehiclePicture $pathVehiclePicture,
        PathGaragePicture $pathGaragePicture,
        PathUserPicture $pathUserPicture,
        PathUserBanner $pathUserBanner
    )
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->placeholders = $placeholders;
        $this->pathVehiclePicture = $pathVehiclePicture;
        $this->pathGaragePicture = $pathGaragePicture;
        $this->pathUserPicture = $pathUserPicture;
        $this->pathUserBanner = $pathUserBanner;
    }

    public function getFilters()
    {
        return array(
            new TwigFilter('avatar', array($this, 'avatarFilter')),
            new TwigFilter('userBanner', array($this, 'userBannerFilter')),
            new TwigFilter('banner', array($this, 'bannerFilter')),
            new TwigFilter('logo', array($this, 'logoFilter')),
            new TwigFilter('vehiclePicture', array($this, 'vehiclePictureFilter')),
            new TwigFilter('defaultVehiclePicture', array($this, 'defaultVehiclePictureFilter')),
            new TwigFilter('defaultAvatar', array($this, 'defaultAvatarFilter')),
            new TwigFilter('defaultUserBanner', array($this, 'defaultUserBannerFilter')),
            new TwigFilter('defaultBanner', array($this, 'defaultBannerFilter')),
            new TwigFilter('defaultLogo', array($this, 'defaultLogoFilter')),
            new TwigFilter('defaultVehicleFormPicture', array($this, 'defaultVehicleFormPictureFilter'))
        );
    }

    public function avatarFilter(?UserPicture $userPicture, string $filter, string $firstname = null)
    {
        return $this->pathUserPicture->getPath($userPicture, $filter, AccentuationUtils::remove($firstname));
    }

    public function defaultAvatarFilter(string $filter)
    {
        return $this->pathUserPicture->getPath(null, $filter, null);
    }

    public function userBannerFilter(?UserBanner $userBanner, string $filter)
    {
        return $this->pathUserBanner->getPath($userBanner, $filter);
    }

    public function defaultUserBannerFilter(string $filter)
    {
        return $this->pathUserBanner->getPath(null, $filter);
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
