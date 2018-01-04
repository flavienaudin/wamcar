<?php


namespace AppBundle\Twig;

use Twig\Extension\AbstractExtension;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;
use Wamcar\User\BaseUser;

class PictureExtension extends AbstractExtension
{
    /** @var UploaderHelper */
    protected $uploaderHelper;
    /** @var array */
    protected $placeholder;

    /**
     * PictureExtension constructor.
     * @param UploaderHelper $uploaderHelper
     */
    public function __construct(UploaderHelper $uploaderHelper, array $placeholder)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->placeholder = $placeholder;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('avatar', array($this, 'avatarFilter')),
        );
    }

    public function avatarFilter(BaseUser $user)
    {
        $picturePath = $user->getAvatar() ? $this->uploaderHelper->asset($user->getAvatar(), 'file'): $this->placeholder['avatar'];

        return $picturePath;
    }
}
