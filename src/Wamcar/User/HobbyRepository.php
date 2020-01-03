<?php


namespace Wamcar\User;


interface HobbyRepository
{
    /**
     * Finds all entities in the repository.
     *
     * @return array The entities.
     */
    public function findAll();


    /**
     * @param Hobby $hobby
     * @return boolean
     */
    public function remove(Hobby $hobby);

}