<?php


namespace AppBundle\Services\VideoCoaching;


use AppBundle\Form\DTO\VideoProjectDTO;
use AppBundle\Form\DTO\VideoProjectMessageDTO;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectMessage;
use Wamcar\VideoCoaching\VideoProjectMessageRepository;
use Wamcar\VideoCoaching\VideoProjectRepository;
use Wamcar\VideoCoaching\VideoProjectViewer;

class VideoProjectService
{

    /** @var VideoProjectRepository */
    private $videoProjectRepository;

    /** @var VideoProjectMessageRepository */
    private $videoProjectMessageRepository;

    /**
     * VideoProjectService constructor.
     * @param VideoProjectRepository $videoProjectRepository
     * @param VideoProjectMessageRepository $videoProjectMessageRepository
     */
    public function __construct(VideoProjectRepository $videoProjectRepository, VideoProjectMessageRepository $videoProjectMessageRepository)
    {
        $this->videoProjectRepository = $videoProjectRepository;
        $this->videoProjectMessageRepository = $videoProjectMessageRepository;
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

    /**
     * @param VideoProjectMessageDTO $messageDTO Les données du message
     */
    public function addMessage(VideoProjectMessageDTO $messageDTO)
    {
        $videoProjectMessage = new VideoProjectMessage();
        $videoProjectMessage->setVideoProject($messageDTO->getVideoProject());
        $videoProjectMessage->setAuthor($messageDTO->getAuthor());
        $videoProjectMessage->setContent($messageDTO->getContent());

        $this->videoProjectMessageRepository->add($videoProjectMessage);
    }

    /**
     * @param VideoProject $videoProject
     * @param \DateTime|null $start
     * @param \DateTime|null $end
     * @return mixed
     */
    public function getMessages(VideoProject $videoProject, ?\DateTime $start, ?\DateTime $end)
    {
        return $this->videoProjectMessageRepository->findByVideoProjectAndTimeInterval($videoProject, $start, $end);
    }
}