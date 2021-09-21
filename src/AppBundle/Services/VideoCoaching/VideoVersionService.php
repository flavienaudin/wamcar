<?php


namespace AppBundle\Services\VideoCoaching;


use AppBundle\Form\DTO\VideoVersionDTO;
use Wamcar\VideoCoaching\VideoVersion;
use Wamcar\VideoCoaching\VideoVersionRepository;

class VideoVersionService
{

    /** @var VideoVersionRepository */
    private $videoVersionRepository;

    /**
     * VideoVersionService constructor.
     * @param VideoVersionRepository $videoVersionRepository
     */
    public function __construct(VideoVersionRepository $videoVersionRepository)
    {
        $this->videoVersionRepository = $videoVersionRepository;
    }

    /**
     * @param VideoVersionDTO $videoVersionDTO Les informations de la version de la vidéo
     * @return VideoVersion
     */
    public function create(VideoVersionDTO $videoVersionDTO)
    {
        $videoVersion = new VideoVersion();
        $videoVersion->setVideoProjectIteration($videoVersionDTO->getVideoProjectIteration());
        $videoVersion->setTitle($videoVersionDTO->getTitle());
        $videoVersion->setYoutubeVideoUrl($videoVersionDTO->getUrl());
        $this->videoVersionRepository->add($videoVersion);
        return $videoVersion;
    }

    /**
     * @param VideoVersionDTO $videoVersionDTO Les informations de la version de la vidéo
     * @param VideoVersion $videoVersion La version de la vidéo à éditer
     * @return VideoVersion
     */
    public function update(VideoVersionDTO $videoVersionDTO, VideoVersion $videoVersion)
    {
        $videoVersion->setTitle($videoVersionDTO->getTitle());
        $videoVersion->setYoutubeVideoUrl($videoVersionDTO->getUrl());
        $this->videoVersionRepository->update($videoVersion);
        return $videoVersion;
    }

    /**
     * @param VideoVersion $videoVersion La version de la vidéo à supprimer.
     */
    public function delete(VideoVersion $videoVersion)
    {
        $this->videoVersionRepository->remove($videoVersion);
    }
}