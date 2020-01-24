<?php


namespace AppBundle\Services\User;


use GoogleApi\GoogleYoutubeApiService;
use Wamcar\User\VideosInsert;
use Wamcar\User\YoutubePlaylistInsert;

class UserVideosInsertService
{

    /** @var GoogleYoutubeApiService $gooleYoutubeApliService */
    private $gooleYoutubeApliService;

    /**
     * UserVideosInsertService constructor.
     * @param GoogleYoutubeApiService $gooleYoutubeApliService
     */
    public function __construct(GoogleYoutubeApiService $gooleYoutubeApliService)
    {
        $this->gooleYoutubeApliService = $gooleYoutubeApliService;
    }

    /**
     * @param VideosInsert $videosInsert
     * @param string|null $pageToken
     * @return VideosInsert
     */
    public function getVideosInsertData(VideosInsert $videosInsert, string $pageToken = null)
    {
        if ($videosInsert instanceof YoutubePlaylistInsert) {
            $playlistData = $this->gooleYoutubeApliService->fetchPlaylistVideos($videosInsert->getPlaylistId(), $pageToken);
            $videosInsert->setPlaylistData($playlistData);
        }
        return $videosInsert;
    }
}