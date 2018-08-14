<?php


namespace Wamcar\User\Event;


use Wamcar\User\Project;

interface ProjectEvent
{
    /**
     * ProjectEvent constructor.
     * @param Project $project
     */
    public function __construct(Project $project);

    /**
     * @return Project
     */
    public function getProject(): Project;
}
