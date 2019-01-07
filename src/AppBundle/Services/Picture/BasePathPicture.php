<?php


namespace AppBundle\Services\Picture;

use AppBundle\Doctrine\Entity\ApplicationPicture;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vich\UploaderBundle\Templating\Helper\UploaderHelper;

abstract class BasePathPicture
{
    /** @var UploaderHelper */
    protected $uploaderHelper;
    /** @var array */
    protected $placeholders;
    /** @var CacheManager */
    protected $imagineCacheManager;

    public function __construct(UploaderHelper $uploaderHelper, array $placeholders, CacheManager $imagineCacheManager)
    {
        $this->uploaderHelper = $uploaderHelper;
        $this->placeholders = $placeholders;
        $this->imagineCacheManager = $imagineCacheManager;
    }

    /**
     * @param ApplicationPicture $picture
     * @param string $filter
     * @param string $fileField
     * @param string $placeholderKey
     * @return string
     */
    protected function getPicturePath(?ApplicationPicture $picture, string $filter, string $fileField, string $placeholderKey): string
    {
        $picturePath = $picture ? $this->uploaderHelper->asset($picture, $fileField): $this->placeholders[$placeholderKey];

        return $this->imagineCacheManager->getBrowserPath($picturePath, $filter);

    }

    /**
     * @param ApplicationPicture $picture
     * @param string $filter
     * @param string $fileField
     * @param string $placeholderKey
     * @param null|string $firstname
     * @return string
     */
    protected function getUserPicturePath(?ApplicationPicture $picture, string $filter, string $fileField, string $placeholderKey, ?string $firstname = null): string
    {
        if($firstname){
            $initial = strtolower(substr($firstname, 0, 1));
        }else {
            $initial = 'unknown';
        }
        $picturePath = $picture ? $this->uploaderHelper->asset($picture, $fileField): $this->placeholders[$placeholderKey][$initial];

        return $this->imagineCacheManager->getBrowserPath($picturePath, $filter);

    }

    /**
     * @param string $filter
     * @param $index
     * @return null|string
     */
    protected function getFormPicturePathPlaceholder(string $filter, $index): ?string
    {
        if (isset($this->placeholders['form_vehicle'][$index])) {
            $picturePath = $this->placeholders['form_vehicle'][$index];

            return $this->imagineCacheManager->getBrowserPath($picturePath, $filter);
        } else {
            return null;
        }

    }

    /**
     * @param string $filter
     * @param string $placeholder
     * @return null|string
     */
    public function getPicturePathPlaceholder(string $filter, string $placeholder): ?string
    {
        if (isset($this->placeholders[$placeholder])) {
            $picturePath = $this->placeholders[$placeholder];

            return $this->imagineCacheManager->getBrowserPath($picturePath, $filter);
        } else {
            return null;
        }

    }
}
