<?php


namespace AppBundle\Services\VideoCoaching;


use AppBundle\Doctrine\Entity\VideoProjectBanner;
use AppBundle\Doctrine\Repository\DoctrineProUserRepository;
use AppBundle\Form\DTO\VideoProjectDTO;
use AppBundle\Form\DTO\VideoProjectMessageDTO;
use AppBundle\Services\Conversation\ConversationEditionService;
use SimpleBus\Message\Bus\MessageBus;
use Symfony\Component\Console\Style\SymfonyStyle;
use Wamcar\User\ProUser;
use Wamcar\VideoCoaching\Event\VideoProjectMessagePostedEvent;
use Wamcar\VideoCoaching\Event\VideoProjectSharingSuccessEvent;
use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectIteration;
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

    /** @var ConversationEditionService */
    private $conversationEditionService;

    /**
     * VideoProjectService constructor.
     * @param VideoProjectRepository $videoProjectRepository
     * @param VideoProjectMessageRepository $videoProjectMessageRepository
     * @param VideoProjectViewerRepository $videoProjectViewRepository
     * @param DoctrineProUserRepository $proUserRepository
     * @param MessageBus $eventBus
     * @param ConversationEditionService $conversationEditionService
     */
    public function __construct(VideoProjectRepository $videoProjectRepository,
                                VideoProjectMessageRepository $videoProjectMessageRepository,
                                VideoProjectViewerRepository $videoProjectViewRepository,
                                DoctrineProUserRepository $proUserRepository,
                                MessageBus $eventBus,
                                ConversationEditionService $conversationEditionService)
    {
        $this->videoProjectRepository = $videoProjectRepository;
        $this->videoProjectMessageRepository = $videoProjectMessageRepository;
        $this->videoProjectViewRepository = $videoProjectViewRepository;
        $this->proUserRepository = $proUserRepository;
        $this->eventBus = $eventBus;
        $this->conversationEditionService = $conversationEditionService;
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
        $videoProject->addVideoProjectIteration(new VideoProjectIteration($videoProject, $videoProjectDTO->getTitle()));
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
     * @param VideoProjectDTO $videoProjectDTO Les informations du projet vidéo
     * @param VideoProject $videoProject Le projet vidéo à éditer
     * @return VideoProject
     * @throws \Exception
     */
    public function updateBanner(VideoProjectDTO $videoProjectDTO, VideoProject $videoProject)
    {
        if ($videoProjectDTO->getBanner()) {
            if ($videoProjectDTO->getBanner()->isRemoved) {
                $videoProject->setBanner(null);
            } elseif ($videoProjectDTO->getBanner()->file) {
                $picture = new VideoProjectBanner($videoProject, $videoProjectDTO->getBanner()->file);
                $videoProject->setBanner($picture);
            }
        }
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
     * @param array $coworkers
     */
    public function updateVideoProjectCoworkers(VideoProject $videoProject, array $coworkers)
    {
        $viewersToKeep = [];
        $newFollowers = [];
        /** @var ProUser $coworker */
        foreach ($coworkers as $coworker) {
            $actualViewerToKeep = $videoProject->getViewerInfo($coworker);
            if ($actualViewerToKeep) {
                $viewersToKeep[] = $actualViewerToKeep;
            } else {
                /** @var VideoProjectViewer $existingSoftDeletedViewer */
                $existingSoftDeletedViewer = $this->videoProjectViewRepository->findIgnoreSoftDeleted(['videoProject' => $videoProject, 'viewer' => $coworker]);
                if ($existingSoftDeletedViewer) {
                    $existingSoftDeletedViewer->setDeletedAt(null);
                    $newFollower = $existingSoftDeletedViewer;
                } else {
                    $newFollower = new VideoProjectViewer($videoProject, $coworker, false);
                }

                $videoProject->addViewer($newFollower);
                $newFollowers[$coworker->getEmail()] = $newFollower;
                $viewersToKeep[] = $newFollower;
            }
        }
        /** @var VideoProjectViewer $videoProjectViewer */
        foreach ($videoProject->getViewers() as $videoProjectViewer) {
            $toKeep = $videoProjectViewer->isCreator();

            // TODO : do not delete coach / wamcar coach when implemnted

            /** @var VideoProjectViewer $viewerToKeep */
            foreach ($viewersToKeep as $viewerToKeep) {
                $toKeep |= $viewerToKeep->getViewer()->is($videoProjectViewer->getViewer());
            }
            if (!$toKeep) {
                $videoProject->removeViewer($videoProjectViewer);
            }
        }
        $this->videoProjectRepository->update($videoProject);
        $this->eventBus->handle(new VideoProjectSharingSuccessEvent($videoProject, $newFollowers));
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
            if ($proUser) {
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
     * @throws \Exception
     */
    public function addMessage(VideoProjectMessageDTO $messageDTO)
    {
        $videoProjectMessage = new VideoProjectMessage($messageDTO->getContent(), $messageDTO->getAuthor(), $messageDTO->getVideoProject(), $messageDTO->getAttachments());
        $this->conversationEditionService->treatsMessageLinkPreviews($videoProjectMessage);
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
     * @param SymfonyStyle|null $io
     */
    public function clearAndReloadVideoProjectMessageLinkPreviews(?SymfonyStyle $io)
    {
        $videoProjectMessages = $this->videoProjectMessageRepository->findAll();
        if ($io != null) {
            $io->progressStart(count($videoProjectMessages));
        }

        /** @var VideoProjectMessage $videoProjectMessage */
        foreach ($videoProjectMessages as $index => $videoProjectMessage) {
            $videoProjectMessage->getLinkPreviews()->clear();
            $this->conversationEditionService->treatsMessageLinkPreviews($videoProjectMessage);
            $this->videoProjectMessageRepository->update($videoProjectMessage);
            if ($io != null) {
                $io->progressAdvance();
            }
        }
        if ($io != null) {
            $io->progressFinish();
        }
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