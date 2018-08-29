<?php

namespace Wamcar\User;


interface ProjectRepository
{
    /**
     * @param Project $project
     */
    public function update(Project $project): void;

    /**
     * @param string $id
     * @return Project
     */
    public function find($id);

    /**
     * @param $ids array Array of entities'id
     * @return array
     */
    public function findByIds(array $ids): array;

}
