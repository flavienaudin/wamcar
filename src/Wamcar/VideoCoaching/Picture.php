<?php

namespace Wamcar\VideoCoaching;

abstract class Picture
{
    /** @var null|VideoProject */
    protected $videoProject;

    /**
     * Picture constructor.
     * @param VideoProject $videoProject
     */
    public function __construct(VideoProject $videoProject)
    {
        $this->videoProject = $videoProject;
    }

    /**
     * @return VideoProject|null
     */
    public function getVideoProject(): ?VideoProject
    {
        return $this->videoProject;
    }

    /**
     * @param VideoProject|null $videoProject
     */
    public function setVideoProject(?VideoProject $videoProject): void
    {
        $this->videoProject = $videoProject;
    }
}
