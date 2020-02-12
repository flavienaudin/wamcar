<?php


namespace Wamcar\User;


use AppBundle\Form\DTO\UserYoutubePlaylistInsertDTO;
use GoogleApi\youtube\YoutubePlaylist;

class YoutubePlaylistInsert extends VideosInsert
{
    /** @var string */
    private $playlistId;
    /** @var YoutubePlaylist|null */
    private $playlistData;

    /**
     * YoutubePlaylistInsert constructor.
     * @param BaseUser $user
     * @param UserYoutubePlaylistInsertDTO|null $youtubePlaylistInsertDTO
     */
    public function __construct(BaseUser $user, ?UserYoutubePlaylistInsertDTO $youtubePlaylistInsertDTO = null)
    {
        parent::__construct($user, $youtubePlaylistInsertDTO);
        if ($youtubePlaylistInsertDTO != null) {
            $this->playlistId = $youtubePlaylistInsertDTO->getPlaylistId();
        }
    }

    /**
     * @return string|null
     */
    public function getPlaylistId(): ?string
    {
        return $this->playlistId;
    }

    /**
     * @param string $playlistId
     */
    public function setPlaylistId(string $playlistId): void
    {
        $this->playlistId = $playlistId;
    }

    /**
     * @return YoutubePlaylist|null
     */
    public function getPlaylistData(): ?YoutubePlaylist
    {
        return $this->playlistData;
    }

    /**
     * @param YoutubePlaylist|null $playlistData
     */
    public function setPlaylistData(?YoutubePlaylist $playlistData): void
    {
        $this->playlistData = $playlistData;
    }
}