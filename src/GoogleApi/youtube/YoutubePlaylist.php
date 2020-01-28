<?php


namespace GoogleApi\youtube;


use GoogleApi\GoogleYoutubeApiService;

class YoutubePlaylist
{
    /** @var string|null */
    private $id;
    /** @var string|null */
    private $channelId;
    /** @var string|null */
    private $channelTitle;
    /** @var \Google_Service_YouTube_PageInfo|null */
    private $pageInfos;
    /** @var string|null */
    private $prevPageToken;
    /** @var string|null */
    private $nextPageToken;
    /** @var int */
    private $currentPageIdx;
    /** @var array of YoutubeVideo */
    private $videos = [];

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
    public function getChannelId(): ?string
    {
        return $this->channelId;
    }

    /**
     * @param string|null $channelId
     */
    public function setChannelId(?string $channelId): void
    {
        $this->channelId = $channelId;
    }

    /**
     * @return string|null
     */
    public function getChannelTitle(): ?string
    {
        return $this->channelTitle;
    }

    /**
     * @param string|null $channelTitle
     */
    public function setChannelTitle(?string $channelTitle): void
    {
        $this->channelTitle = $channelTitle;
    }

    /**
     * @return \Google_Service_YouTube_PageInfo|null
     */
    public function getPageInfos(): ?\Google_Service_YouTube_PageInfo
    {
        return $this->pageInfos;
    }

    /**
     * @param \Google_Service_YouTube_PageInfo|null $pageInfos
     */
    public function setPageInfos(?\Google_Service_YouTube_PageInfo $pageInfos): void
    {
        $this->pageInfos = $pageInfos;
    }

    /**
     * @return string|null
     */
    public function getPrevPageToken(): ?string
    {
        return $this->prevPageToken;
    }

    /**
     * @param string|null $prevPageToken
     */
    public function setPrevPageToken(?string $prevPageToken): void
    {
        $this->prevPageToken = $prevPageToken;
    }

    /**
     * @return string|null
     */
    public function getNextPageToken(): ?string
    {
        return $this->nextPageToken;
    }

    /**
     * @param string|null $nextPageToken
     */
    public function setNextPageToken(?string $nextPageToken): void
    {
        $this->nextPageToken = $nextPageToken;
    }

    /**
     * @return int
     */
    public function getCurrentPageIdx(): int
    {
        return $this->currentPageIdx;
    }

    /**
     * @param int $currentPageIdx
     */
    public function setCurrentPageIdx(int $currentPageIdx): void
    {
        $this->currentPageIdx = $currentPageIdx;
    }

    public function getNextTokenVideosNumber(): int
    {
        return $nbRemainingVideos = min(
            $this->getPageInfos()->getTotalResults() - $this->getCurrentPageIdx() * $this->getPageInfos()->getResultsPerPage(),
            GoogleYoutubeApiService::YOUTUBE_PLAYLIST_VIDEOS_BATCH_SIZE
        );
    }

    /**
     * @return array
     */
    public function getVideos(): array
    {
        return $this->videos;
    }

    /**
     * @param array $videos
     */
    public function setVideos(array $videos): void
    {
        $this->videos = $videos;
    }

    /**
     * @param YoutubeVideo $youtubeVideo
     * @param int $position
     */
    public function addVideo(YoutubeVideo $youtubeVideo, int $position)
    {
        $this->videos[$position] = $youtubeVideo;
    }
}