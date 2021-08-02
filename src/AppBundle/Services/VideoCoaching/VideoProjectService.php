<?php


namespace AppBundle\Services\VideoCoaching;


use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Form\DTO\VideoProjectDTO;
use AppBundle\Form\DTO\VideoProjectMessageDTO;
use SimpleBus\Message\Bus\MessageBus;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\Event\VideoProjectMessagePostedEvent;
use Wamcar\VideoCoaching\Event\VideoProjectSharingSuccessEvent;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectMessage;
use Wamcar\VideoCoaching\VideoProjectMessageRepository;
use Wamcar\VideoCoaching\VideoProjectRepository;
use Wamcar\VideoCoaching\VideoProjectViewer;
use Wamcar\VideoCoaching\VideoProjectViewerRepository;

class VideoProjectService
{
    const VIDEOCOACHING_SHARE_VIDEOPROJECT_SUCCESS = "share_videoproject.success";
    const VIDEOCOACHING_SHARE_VIDEOPROJECT_FAIL = "share_videoproject.fail";


    /** @var VideoProjectRepository */
    private $videoProjectRepository;

    /** @var VideoProjectMessageRepository */
    private $videoProjectMessageRepository;

    /** @var VideoProjectViewerRepository */
    private $videoProjectViewRepository;

    /** @var DoctrineProUserRepository */
    private $proUserRepository;

    /** @var MessageBus */
    private $eventBus;

    /**
     * VideoProjectService constructor.
     * @param VideoProjectRepository $videoProjectRepository
     * @param VideoProjectMessageRepository $videoProjectMessageRepository
     * @param VideoProjectViewerRepository $videoProjectViewRepository
     * @param DoctrineProUserRepository $proUserRepository
     * @param MessageBus $eventBus
     */
    public function __construct(VideoProjectRepository $videoProjectRepository,
                                VideoProjectMessageRepository $videoProjectMessageRepository,
                                VideoProjectViewerRepository $videoProjectViewRepository,
                                DoctrineProUserRepository $proUserRepository,
                                MessageBus $eventBus)
    {
        $this->videoProjectRepository = $videoProjectRepository;
        $this->videoProjectMessageRepository = $videoProjectMessageRepository;
        $this->videoProjectViewRepository = $videoProjectViewRepository;
        $this->proUserRepository = $proUserRepository;
        $this->eventBus = $eventBus;
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
     * @param VideoProject $videoProject
     * @param array $emails
     * @return array
     */
    public function shareVideoProjectToUsersByEmails(VideoProject $videoProject, array $emails): array
    {
        $results = [
            self::VIDEOCOACHING_SHARE_VIDEOPROJECT_SUCCESS => [],
            self::VIDEOCOACHING_SHARE_VIDEOPROJECT_FAIL => []
        ];
        foreach ($emails as $email) {
            /** @var ProUser|null $proUser */
            $proUser = $this->proUserRepository->findOneByEmail($email);
            if ($proUser && $proUser->hasVideoModuleAccess()) {
                $follower = new VideoProjectViewer($videoProject, $proUser, false);
                $videoProject->addViewer($follower);
                $results[self::VIDEOCOACHING_SHARE_VIDEOPROJECT_SUCCESS][$email] = $follower;
            } else {
                $results[self::VIDEOCOACHING_SHARE_VIDEOPROJECT_FAIL][] = $email;
            }
        }
        $this->videoProjectRepository->update($videoProject);
        $this->eventBus->handle(new VideoProjectSharingSuccessEvent($videoProject, $results[self::VIDEOCOACHING_SHARE_VIDEOPROJECT_SUCCESS]));
        return $results;
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

        $this->eventBus->handle(new VideoProjectMessagePostedEvent($videoProjectMessage));
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

    /**
     * @param VideoProject $videoProject
     * @param ProUser $proUser
     * @return bool
     * @throws \Exception
     */
    public function updateVisitedAt(VideoProject $videoProject, ProUser $proUser)
    {
        $videoProjectViewer = $videoProject->getViewerInfo($proUser);
        if ($videoProjectViewer) {
            $videoProjectViewer->setVisitedAt(new \DateTime());
            $this->videoProjectViewRepository->update($videoProjectViewer);
            return true;
        }
        return false;
    }
}