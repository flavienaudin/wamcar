<?php


namespace AppBundle\Twig;

use AppBundle\Doctrine\Entity\VehiclePicture;
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


    /**
     * PictureExtension constructor.
     * @param UploaderHelper $uploaderHelper
     * @param array $placeholders
     * @param PathVehiclePicture $pathVehiclePicture
     */
    public function __construct(UploaderHelper $uploaderHelper, array $placeholders, PathVehiclePicture $pathVehiclePicture)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->placeholders = $placeholders;
        $this->pathVehiclePicture = $pathVehiclePicture;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('avatar', array($this, 'avatarFilter')),
            new \Twig_SimpleFilter('banner', array($this, 'bannerFilter')),
            new \Twig_SimpleFilter('logo', array($this, 'logoFilter')),
            new \Twig_SimpleFilter('vehiclePicture', array($this, 'vehiclePictureFilter'))
        );
    }

    public function avatarFilter(BaseUser $user)
    {
        $picturePath = $user->getAvatar() ? $this->uploaderHelper->asset($user->getAvatar(), 'file'): $this->placeholders['avatar'];

        return $picturePath;
    }

    public function bannerFilter(?Garage $garage)
    {
        $picturePath = $garage && $garage->getBanner() ? $this->uploaderHelper->asset($garage->getBanner(), 'file'): $this->placeholders['banner'];

        return $picturePath;
    }

    public function logoFilter(?Garage $garage)
    {
        $picturePath = $garage && $garage->getLogo() ? $this->uploaderHelper->asset($garage->getLogo(), 'file'): $this->placeholders['logo'];

        return $picturePath;
    }

    public function vehiclePictureFilter(?VehiclePicture $vehiclePicture)
    {
        $picturePath = $vehiclePicture ? $this->uploaderHelper->asset($vehiclePicture, 'file'): $this->placeholders['vehicle'];

        return $picturePath;
    }
}
