<?php


namespace AppBundle\Form\DTO;


use Wamcar\VideoCoaching\VideoProjectIteration;
use Wamcar\VideoCoaching\VideoVersion;

class VideoVersionDTO
{
    /** @var VideoProjectIteration */
    private $videoProjectIteration;
    /** @var null|string */
    private $title;
    /** @var null|string */
    private $url;

    /**
     * VideoVersionDTO constructor.
     * @param VideoProjectIteration $videoProjectIteration
     */
    public function __construct(VideoProjectIteration $videoProjectIteration)
    {
        $this->videoProjectIteration = $videoProjectIteration;
    }

    public static function buildFromVideoVersion(VideoVersion $videoVersion)
    {
        $videoVideoDTO = new VideoVersionDTO($videoVersion->getVideoProjectIteration());
        $videoVideoDTO->title = $videoVersion->getTitle();
        $videoVideoDTO->url = $videoVersion->getYoutubeVideoUrl();
        return $videoVideoDTO;
    }

    /**
     * @return VideoProjectIteration
     */
    public function getVideoProjectIteration(): VideoProjectIteration
    {
        return $this->videoProjectIteration;
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