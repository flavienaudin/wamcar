<?php


namespace AppBundle\Form\DTO;


use Wamcar\VideoCoaching\VideoProject;
use Wamcar\VideoCoaching\VideoProjectViewer;

class VideoProjectViewersDTO
{

    /** @var VideoProjectViewer[] $coworkers */
    private $coworkers;

    /**
     * VideoProjectViewersDTO constructor.
     * @param VideoProject $videoProject
     */
    public function __construct(VideoProject $videoProject)
    {
        $this->coworkers = [];
        /** @var VideoProjectViewer $viewer */
        foreach ($videoProject->getViewers(true) as $viewer) {
            $this->coworkers[$viewer->getViewer()->getId()] = $viewer->getViewer();
        }
    }

    /**
     * @return VideoProjectViewer[]
     */
    public function getCoworkers(): array
    {
        return $this->coworkers;
    }

    /**
     * @param VideoProjectViewer[] $coworkers
     */
    public function setCoworkers(array $coworkers): void
    {
        $this->coworkers = $coworkers;
    }

    /**
     * @param VideoProjectViewer $videoProjectViewer
     */
    public function addCoworker(VideoProjectViewer $videoProjectViewer)
    {
        $this->coworkers[] = $videoProjectViewer;
    }
}