<?php


namespace AppBundle\Services\VideoCoaching;


use AppBundle\Form\DTO\VideoProjectDTO;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectRepository;
use Wamcar\VideoCoaching\VideoProjectViewer;

class VideoProjectService
{

    /** @var VideoProjectRepository */
    private $videoProjectRepository;

    /**
     * VideoProjectService constructor.
     * @param VideoProjectRepository $videoProjectRepository
     */
    public function __construct(VideoProjectRepository $videoProjectRepository)
    {
        $this->videoProjectRepository = $videoProjectRepository;
    }

    /**
     * @param VideoProjectDTO $videoProjectDTO Les informations du projet vidéo
     * @param ProUser $owner Le porteur du projet
     * @return VideoProject
     */
    public function create(VideoProjectDTO $videoProjectDTO, ProUser $owner)
    {
        $videoProject = new VideoProject();
        $videoProject->addViewer(new VideoProjectViewer($videoProject, $owner, true));
        $videoProject->setTitle($videoProjectDTO->getTitle());
        $videoProject->setDescription($videoProjectDTO->getDescription());
        $this->videoProjectRepository->add($videoProject);
        return $videoProject;
    }

    /**
     * @param VideoProjectDTO $videoProjectDTO Les informations du projet vidéo
     * @param VideoProject $videoProject Le projet vidéo à éditer
     * @return VideoProject
     */
    public function update(VideoProjectDTO $videoProjectDTO, VideoProject $videoProject)
    {
        $videoProject->setTitle($videoProjectDTO->getTitle());
        $videoProject->setDescription($videoProjectDTO->getDescription());
        $this->videoProjectRepository->update($videoProject);
        return $videoProject;
    }

    /**
     * @param VideoProject $videoProject Le projet vidéo à supprimer.
     */
    public function delete(VideoProject $videoProject)
    {
        // TODO vérifier s'il y a des associations à supprimer manuellement
        $this->videoProjectRepository->remove($videoProject);
    }
}