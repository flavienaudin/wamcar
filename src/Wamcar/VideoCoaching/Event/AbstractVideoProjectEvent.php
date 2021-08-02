<?php


namespace Wamcar\VideoCoaching\Event;


use Wamcar\VideoCoaching\VideoProject;

class AbstractVideoProjectEvent
{

    /** @var VideoProject $videoProject */
    private $videoProject;

    /**
     * AbstractVideoProjectEvent constructor.
     * @param VideoProject $videoProject
     */
    public function __construct(VideoProject $videoProject)
    {
        $this->videoProject = $videoProject;
    }


    /**
     * @return VideoProject
     */
    public function getVideoProject(): VideoProject
    {
        return $this->videoProject;
    }
}