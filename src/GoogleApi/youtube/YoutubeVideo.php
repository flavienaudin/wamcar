<?php


namespace GoogleApi\youtube;


class YoutubeVideo
{

    /** @var string|null */
    private $id;
    /** @var string|null */
    private $title;
    /** @var \Google_Service_YouTube_ThumbnailDetails|null */
    private $thumbnailDetails;
    /** @var \DateTime|null */
    private $publishedAt;
    /** @var YoutubePlaylist|null */
    private $playlist;
    /** @var int|null */
    private $playlistPosition;
    /** @var \Google_Service_YouTube_VideoStatistics|null */
    private $videoStatistics;

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string|null $title
     */
    public function setTitle(?string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return \Google_Service_YouTube_ThumbnailDetails|null
     */
    public function getThumbnailDetails(): ?\Google_Service_YouTube_ThumbnailDetails
    {
        return $this->thumbnailDetails;
    }

    /**
     * @param \Google_Service_YouTube_ThumbnailDetails|null $thumbnailDetails
     */
    public function setThumbnailDetails(?\Google_Service_YouTube_ThumbnailDetails $thumbnailDetails): void
    {
        $this->thumbnailDetails = $thumbnailDetails;
    }

    /**
     * @return \DateTime|null
     */
    public function getPublishedAt(): ?\DateTime
    {
        return $this->publishedAt;
    }

    /**
     * @param \DateTime|null $publishedAt
     */
    public function setPublishedAt(?\DateTime $publishedAt): void
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * @return YoutubePlaylist|null
     */
    public function getPlaylist(): ?YoutubePlaylist
    {
        return $this->playlist;
    }

    /**
     * @param YoutubePlaylist|null $playlist
     */
    public function setPlaylist(?YoutubePlaylist $playlist): void
    {
        $this->playlist = $playlist;
    }

    /**
     * @return int|null
     */
    public function getPlaylistPosition(): ?int
    {
        return $this->playlistPosition;
    }

    /**
     * @param int|null $playlistPosition
     */
    public function setPlaylistPosition(?int $playlistPosition): void
    {
        $this->playlistPosition = $playlistPosition;
    }

    /**
     * @return \Google_Service_YouTube_VideoStatistics|null
     */
    public function getVideoStatistics(): ?\Google_Service_YouTube_VideoStatistics
    {
        return $this->videoStatistics;
    }

    /**
     * @param \Google_Service_YouTube_VideoStatistics|null $videoStatistics
     */
    public function setVideoStatistics(?\Google_Service_YouTube_VideoStatistics $videoStatistics): void
    {
        $this->videoStatistics = $videoStatistics;
    }
}