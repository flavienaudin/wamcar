<?php

namespace Wamcar\User\Event;

use Wamcar\User\Project;

abstract class AbstractProjectEvent
{
    /** @var Project */
    private $project;

    /**
     * AbstractProjectEvent constructor.
     * @param Project $project
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }
}
