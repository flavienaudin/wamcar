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
     * Finds all entities in the repository.
     *
     * @return array
     */
    public function findAll();

    /**
     * @param $ids array Array of entities'id
     * @return array
     */
    public function findByIds(array $ids): array;

}
