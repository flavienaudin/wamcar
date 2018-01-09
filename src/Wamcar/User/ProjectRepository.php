<?php

namespace Wamcar\User;


interface ProjectRepository
{
    /**
     * @param Project $project
     */
    public function update(Project $project): void;

}
