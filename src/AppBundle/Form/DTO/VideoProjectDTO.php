<?php


namespace AppBundle\Form\DTO;


use Wamcar\VideoCoaching\VideoProject;

class VideoProjectDTO
{
    /** @var null|string */
    private $title;
    /** @var null|string */
    private $description;
    /** @var VideoProjectPictureDTO */
    private $banner;

    public static function buildFromVideoProject(VideoProject $videoProject)
    {
        $videoProjectDTO = new VideoProjectDTO();
        $videoProjectDTO->title = $videoProject->getTitle();
        $videoProjectDTO->description = $videoProject->getDescription();
        $videoProjectDTO->banner = new VideoProjectPictureDTO($videoProject->getBannerFile());
        return $videoProjectDTO;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string|null $description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return VideoProjectPictureDTO
     */
    public function getBanner(): VideoProjectPictureDTO
    {
        return $this->banner;
    }

    /**
     * @param VideoProjectPictureDTO $banner
     */
    public function setBanner(VideoProjectPictureDTO $banner): void
    {
        $this->banner = $banner;
    }

}