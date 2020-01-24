<?php


namespace GoogleApi;


use GoogleApi\youtube\YoutubePlaylist;
use GoogleApi\youtube\YoutubeVideo;

class GoogleYoutubeApiService
{

    /** @var string */
    private $applicationName;
    /** @var \Google_Service_YouTube */
    private $youtubeApi;

    /**
     * GoogleYoutubeApiService constructor.
     * @param string $applicationName
     */

    public function __construct(string $applicationName)
    {
        $this->applicationName = $applicationName;

        $client = new \Google_Client();
        $client->setApplicationName($this->applicationName);
        $client->useApplicationDefaultCredentials();
        $client->setScopes(\Google_Service_YouTube::YOUTUBE_READONLY);

        $this->youtubeApi = new \Google_Service_YouTube($client);
    }

    /**
     * Retrieve playlist's videos and data about playlist
     * @param string $playlistId
     * @param null|string $pageToken
     * @return \Google_Service_YouTube_PlaylistListResponse
     */
    public function fetchPlayListData(string $playlistId, string $pageToken = null)
    {
        $options = [
            'id' => $playlistId,
            'maxResults' => 5
        ];
        if (!empty($pageToken)) {
            $options['pageToken'] = $pageToken;
        }

        /** @var \Google_Service_YouTube_PlaylistListResponse $playlistListResponse */
        $playlistListResponse = $this->youtubeApi->playlists->listPlaylists('snippet', $options);
        return $playlistListResponse;
    }

    /**
     * Retrieve playlist's videos and data about playlist
     * @param string $playlistId
     * @param null|string $pageToken
     * @return YoutubePlaylist
     */
    public function fetchPlaylistVideos(string $playlistId, string $pageToken = null)
    {
        $options = [
            'playlistId' => $playlistId,
            'maxResults' => 6
        ];
        if (!empty($pageToken)) {
            $options['pageToken'] = $pageToken;
        }

        /** @var \Google_Service_YouTube_PlaylistItemListResponse $playlistListResponse */
        $playlistListResponse = $this->youtubeApi->playlistItems->listPlaylistItems('snippet', $options);
        return $this->convertPlaylistListResponseToYoutubePlaylist($playlistListResponse);
    }

    /**
     * @param \Google_Service_YouTube_PlaylistItemListResponse $playlistItemListResponse
     * @return YoutubePlaylist
     */
    private function convertPlaylistListResponseToYoutubePlaylist(\Google_Service_YouTube_PlaylistItemListResponse $playlistItemListResponse): YoutubePlaylist
    {
        $youtubePlaylist = new YoutubePlaylist();
        $youtubePlaylist->setPageInfos($playlistItemListResponse->getPageInfo());
        $youtubePlaylist->setPrevPageToken($playlistItemListResponse->getPrevPageToken());
        $youtubePlaylist->setNextPageToken($playlistItemListResponse->getNextPageToken());

        /* Videos data */
        $videoIdsPositions = [];
        /** @var \Google_Service_YouTube_PlaylistItem $playlistItem */
        foreach ($playlistItemListResponse->getItems() as $playlistItem) {
            if (!empty($playlistItem->getSnippet())) {

                /* Playlist data (set ONLY once with the first video data) */
                if (empty($youtubePlaylist->getId())) {
                    $youtubePlaylist->setId($playlistItem->getSnippet()->getPlaylistId());
                }
                if (empty($youtubePlaylist->getChannelId())) {
                    $youtubePlaylist->setChannelId($playlistItem->getSnippet()->getChannelId());
                }
                if (empty($youtubePlaylist->getChannelTitle())) {
                    $youtubePlaylist->setChannelTitle($playlistItem->getSnippet()->getChannelTitle());
                }

                // Video data
                if (!empty($playlistItem->getSnippet()->getResourceId()) && !empty($playlistItem->getSnippet()->getResourceId()->getVideoId())) {
                    $youtubeVideo = new YoutubeVideo();
                    $youtubeVideo->setId($playlistItem->getSnippet()->getResourceId()->getVideoId());
                    $youtubeVideo->setPlaylist($youtubePlaylist);
                    $youtubeVideo->setPlaylistPosition($playlistItem->getSnippet()->getPosition());

                    if (!empty($playlistItem->getSnippet()->getTitle())) {
                        $youtubeVideo->setTitle($playlistItem->getSnippet()->getTitle());
                    }
                    if (!empty($playlistItem->getSnippet()->getThumbnails())) {
                        $youtubeVideo->setThumbnailDetails($playlistItem->getSnippet()->getThumbnails());
                    }
                    if (!empty($playlistItem->getSnippet()->getPublishedAt())) {
                        try {
                            $youtubeVideo->setPublishedAt(new \DateTime($playlistItem->getSnippet()->getPublishedAt()));
                        } catch (\Exception $e) {
                            // No problem, do not set PublishedAt
                        }
                    }

                    if ($youtubeVideo->getPlaylistPosition() !== null) {
                        $youtubePlaylist->addVideo($youtubeVideo, $youtubeVideo->getPlaylistPosition());
                        // To fetch the data later
                        if (isset($videoIdsPositions[$youtubeVideo->getId()])) {
                            // When a video is many times in a playlist
                            $videoIdsPositions[$youtubeVideo->getId()] .= ',' . $youtubeVideo->getPlaylistPosition();
                        } else {
                            $videoIdsPositions[$youtubeVideo->getId()] = $youtubeVideo->getPlaylistPosition();
                        }

                    }
                }
            }
        }
        $this->fetchPlaylistVideosData($videoIdsPositions, $youtubePlaylist);
        return $youtubePlaylist;
    }

    /**
     * @param array $videoIdsPositions with key as Youtube Video Id and value as Playlist position
     * @param YoutubePlaylist $playlist The YoutubePlaylistto complete
     */
    public function fetchPlaylistVideosData(array $videoIdsPositions, YoutubePlaylist $playlist)
    {
        /** @var \Google_Service_YouTube_VideoListResponse $videoListResponse */
        $videoListResponse = $this->youtubeApi->videos->listVideos('snippet,statistics', [
            'id' => implode(',', array_keys($videoIdsPositions))
        ]);
        /** @var \Google_Service_YouTube_Video $videoItem */
        foreach ($videoListResponse->getItems() as $videoItem) {
            $videoPositions = $videoIdsPositions[$videoItem->getId()] ?? null;
            foreach (explode(',', $videoPositions) as $videoPosition) {
                if (isset($playlist->getVideos()[$videoPosition])) {
                    /** @var YoutubeVideo $youtubeVideo */
                    $youtubeVideo = $playlist->getVideos()[$videoPosition];
                    if (!empty($videoItem->getStatistics())) {
                        $youtubeVideo->setVideoStatistics($videoItem->getStatistics());
                    }
                }
            }
        }
    }

    /**
     * Retrieve channel's videos and data about the channel
     * @param string $channelId
     * @return \Google_Service_YouTube_SearchListResponse
     */
    public function fetchChannelVideos(string $channelId)
    {
        /** @var \Google_Service_YouTube_SearchListResponse $searchListResponse */
        $searchListResponse = $this->youtubeApi->search->listSearch('snippet', [
            'channelId' => 'UCU0FhLr6fr7U9GOn6OiQHpQ',
            'order' => 'date'
        ]);
        return $searchListResponse;
    }
}