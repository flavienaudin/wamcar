<?php


namespace AppBundle\Services\User;


use AppBundle\Form\DTO\UserVideosInsertDTO;
use AppBundle\Form\DTO\UserYoutubePlaylistInsertDTO;
use GoogleApi\GoogleYoutubeApiService;
use Wamcar\User\BaseUser;
use Wamcar\User\UserRepository;
use Wamcar\User\VideosInsert;
use Wamcar\User\VideosInsertReposistory;
use Wamcar\User\YoutubePlaylistInsert;

class UserVideosInsertService
{

    /** @var GoogleYoutubeApiService $gooleYoutubeApliService */
    private $gooleYoutubeApliService;
    /** @var UserRepository $userRepository */
    private $userRepository;
    /** @var VideosInsertReposistory $videosInsertRepository */
    private $videosInsertRepository;

    /**
     * UserVideosInsertService constructor.
     * @param GoogleYoutubeApiService $gooleYoutubeApliService
     * @param UserRepository $userRepository
     * @param VideosInsertReposistory $videosInsertRepository
     */
    public function __construct(
        GoogleYoutubeApiService $gooleYoutubeApliService,
        UserRepository $userRepository ,
        VideosInsertReposistory $videosInsertRepository)
    {
        $this->gooleYoutubeApliService = $gooleYoutubeApliService;
        $this->userRepository = $userRepository;
        $this->videosInsertRepository = $videosInsertRepository;
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

    /**
     * @param BaseUser $user
     * @param UserVideosInsertDTO $videosInsertDTO
     * @return BaseUser
     */
    public function addVideosInsert(BaseUser $user, UserVideosInsertDTO $videosInsertDTO): BaseUser
    {
        if ($videosInsertDTO instanceof UserYoutubePlaylistInsertDTO) {
            $videosInsert = new YoutubePlaylistInsert($user, $videosInsertDTO);
        }
        if (isset($videosInsert) && $videosInsert != null) {
            $user->addVideosInsert($videosInsert);
            $this->userRepository->update($user);
        }
        return $user;
    }

    /**
     * @param VideosInsert $videosInsert
     * @param UserVideosInsertDTO $videosInsertDTO
     * @return VideosInsert
     */
    public function editVideosInsert(VideosInsert $videosInsert, UserVideosInsertDTO $videosInsertDTO): VideosInsert
    {
        $videosInsert->setTitle($videosInsertDTO->getTitle());
        if($videosInsert instanceof YoutubePlaylistInsert && $videosInsertDTO instanceof UserYoutubePlaylistInsertDTO){
            $videosInsert->setPlaylistId($videosInsertDTO->getPlaylistId());
        }
        $this->videosInsertRepository->update($videosInsert);
        return $videosInsert;
    }

    /**
     * @param VideosInsert $videosInsert
     */
    public function deleteVideosInsert(VideosInsert $videosInsert)
    {
        $this->videosInsertRepository->remove($videosInsert);
    }
}