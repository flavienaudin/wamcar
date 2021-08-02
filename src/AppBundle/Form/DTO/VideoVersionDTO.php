<?php


namespace AppBundle\Form\DTO;


use Wamcar\VideoCoaching\VideoVersion;

class VideoVersionDTO
{
    /** @var null|string */
    private $title;
    /** @var null|string */
    private $url;

    public static function buildFromVideoVersion(VideoVersion $videoVersion)
    {
        $videoVideoDTO = new VideoVersionDTO();
        $videoVideoDTO->title = $videoVersion->getTitle();
        $videoVideoDTO->url = $videoVersion->getYoutubeVideoUrl();
        return $videoVideoDTO;
    }

    /**
     * @return string|null
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
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }
}