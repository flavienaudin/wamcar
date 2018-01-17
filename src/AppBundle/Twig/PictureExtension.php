<?php


namespace AppBundle\Twig;

use AppBundle\Doctrine\Entity\VehiclePicture;
use AppBundle\Services\Picture\PathGaragePicture;
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


    /**
     * PictureExtension constructor.
     * @param UploaderHelper $uploaderHelper
     * @param array $placeholders
     * @param PathVehiclePicture $pathVehiclePicture
     * @param PathGaragePicture $pathGaragePicture
     */
    public function __construct(
        UploaderHelper $uploaderHelper,
        array $placeholders,
        PathVehiclePicture $pathVehiclePicture,
        PathGaragePicture $pathGaragePicture
    )
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->placeholders = $placeholders;
        $this->pathVehiclePicture = $pathVehiclePicture;
        $this->pathGaragePicture = $pathGaragePicture;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('avatar', array($this, 'avatarFilter')),
            new \Twig_SimpleFilter('banner', array($this, 'bannerFilter')),
            new \Twig_SimpleFilter('logo', array($this, 'logoFilter')),
            new \Twig_SimpleFilter('vehiclePicture', array($this, 'vehiclePictureFilter')),
            new \Twig_SimpleFilter('defaultVehiclePicture', array($this, 'defaultVehiclePictureFilter'))
        );
    }

    public function avatarFilter(BaseUser $user)
    {
        $picturePath = $user->getAvatar() ? $this->uploaderHelper->asset($user->getAvatar(), 'file'): $this->placeholders['avatar'];

        return $picturePath;
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
