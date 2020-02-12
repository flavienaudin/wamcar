<?php


namespace AppBundle\Form\DTO;


use Wamcar\User\YoutubePlaylistInsert;

class UserYoutubePlaylistInsertDTO extends UserVideosInsertDTO
{
    /** @var string|null */
    private $playlistId;

    /**
     * UserYoutubePlaylistInsertDTO constructor.
     */
    public function __construct(YoutubePlaylistInsert $youtubePlaylistInsert)
    {
        parent::__construct($youtubePlaylistInsert);
        $this->playlistId = $youtubePlaylistInsert->getPlaylistId();
    }

    /**
     * @return string|null
     */
    public function getPlaylistId()
    {
        return $this->playlistId;
    }

    /**
     * @param null|string $playlistId
     */
    public function setPlaylistId(?string $playlistId): void
    {
        $this->playlistId = $playlistId;
    }
}