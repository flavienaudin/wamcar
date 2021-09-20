<?php


namespace Wamcar\VideoCoaching;


use Gedmo\SoftDeleteable\Traits\SoftDeleteable;
use Gedmo\Timestampable\Traits\Timestampable;
use Ramsey\Uuid\Uuid;


class VideoVersion
{
    use SoftDeleteable;
    use Timestampable;

    /** @var string */
    private $id;
    /** @var string */
    private $title;
    /** @var string */
    private $youtubeVideoUrl;
    /** @var VideoProjectIteration */
    private $videoProjectIteration;

    /**
     * VideoVersion constructor.
     */
    public function __construct()
    {
        $this->id = Uuid::uuid4();
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
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
     * @return string
     */
    public function getYoutubeVideoUrl(): string
    {
        return $this->youtubeVideoUrl;
    }

    /**
     * @return string|null
     */
    public function getYoutubeVideoId(): ?string
    {

        $youtubeIds = [];
        preg_match('/(https:\/\/www.youtube.com\/watch\?v=|https:\/\/youtu.be\/){1}([^&]+)/',
            $this->youtubeVideoUrl,
            $youtubeIds);
        return isset($youtubeIds[2]) ? $youtubeIds[2] : null;
    }

    /**
     * @param string $youtubeVideoUrl
     */
    public function setYoutubeVideoUrl(string $youtubeVideoUrl): void
    {
        $this->youtubeVideoUrl = $youtubeVideoUrl;
    }

    /**
     * @return VideoProjectIteration
     */
    public function getVideoProjectIteration(): VideoProjectIteration
    {
        return $this->videoProjectIteration;
    }

    /**
     * @param VideoProjectIteration $videoProjectIteration
     */
    public function setVideoProjectIteration(VideoProjectIteration $videoProjectIteration): void
    {
        $this->videoProjectIteration = $videoProjectIteration;
    }
}