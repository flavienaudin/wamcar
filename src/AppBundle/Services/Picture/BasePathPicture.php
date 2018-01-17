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
}
